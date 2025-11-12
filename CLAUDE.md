# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

VrtxCRM is a **multi-tenant CRM platform** with enterprise-grade features including:
- **Dynamic Module Builder**: Create custom modules and entities at runtime
- **Workflow Engine**: Visual workflow builder for business process automation
- **Automation System**: Event-driven automations and triggers
- **Multi-Tenancy**: Isolated tenant data and configurations

### Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Svelte 5 + Inertia.js 2
- **UI Components**: shadcn-svelte
- **Styling**: Tailwind CSS v4
- **Type Safety**: TypeScript with strict mode enabled

This is a full-stack application that uses Inertia.js to bridge Laravel backend with Svelte frontend, providing an SPA-like experience without building a separate API.

## Development Commands

### Starting Development Environment
```bash
composer dev
```
This runs all development services concurrently:
- Laravel dev server (port 8000)
- Queue worker
- Pail (log viewer)
- Vite dev server (port 5173)

### Running Individual Services
```bash
php artisan serve           # Start Laravel server
npm run dev                 # Start Vite dev server only
php artisan queue:listen    # Start queue worker
php artisan pail            # Start log viewer
```

### Building for Production
```bash
npm run build               # Build frontend assets only
npm run build:ssr           # Build with SSR support
```

### Code Quality
```bash
composer pint               # Run Laravel Pint (PHP formatter)
./vendor/bin/pint          # Alternative Pint command
npm run lint                # Run ESLint with auto-fix
npm run format              # Format files with Prettier
npm run format:check        # Check formatting without changes
```

### Testing
```bash
composer test               # Run all PHPUnit tests
php artisan test            # Alternative test command
php artisan test --filter=TestName  # Run specific test
```

Test suites are organized into:
- `tests/Unit/` - Unit tests
- `tests/Feature/` - Feature tests (includes Auth and Settings tests)

## Architecture

### Frontend Structure

#### Page Resolution
Pages are auto-resolved from `resources/js/pages/` using glob pattern matching. Inertia matches controller render calls to `.svelte` files:
- `Inertia::render('Dashboard')` → `resources/js/pages/Dashboard.svelte`
- `Inertia::render('auth/Login')` → `resources/js/pages/auth/Login.svelte`

#### Layout System
Multiple layout patterns available in `resources/js/layouts/`:
- **App Layouts**: `app/AppSidebarLayout.svelte`, `app/AppHeaderLayout.svelte`
- **Auth Layouts**: `auth/AuthCardLayout.svelte`, `auth/AuthSimpleLayout.svelte`, `auth/AuthSplitLayout.svelte`
- **Settings Layout**: `settings/Layout.svelte` (nested layout pattern)

#### Component Organization
- `resources/js/components/ui/` - shadcn-svelte components (button, dialog, form, etc.)
- `resources/js/components/` - Application-specific components (AppSidebar, NavMain, Breadcrumbs, etc.)
- `resources/js/hooks/` - Reusable Svelte 5 runes-based hooks (useAppearance, useInitials, is-mobile)
- `resources/js/lib/` - Utility functions

#### Type Safety
TypeScript types in `resources/js/types/`:
- `index.d.ts` - App-specific types (User, PageProps)
- `global.d.ts` - Global type augmentations
- Path aliases configured: `@/*` maps to `resources/js/*`

### Backend Structure

#### Routing
Routes are organized by concern:
- `routes/web.php` - Main application routes
- `routes/auth.php` - Authentication routes
- `routes/settings.php` - Settings routes
- `routes/console.php` - Artisan commands

#### Inertia Configuration
`HandleInertiaRequests` middleware shares data globally:
- `auth.user` - Current authenticated user
- `name` - Application name
- `quote` - Inspirational quote
- `sidebarOpen` - Sidebar state from cookie

#### Middleware
- `HandleInertiaRequests` - Shares data with Inertia
- `HandleAppearance` - Manages theme (light/dark/system) via cookie

### Multi-Tenancy Architecture

This application uses **Laravel Tenancy (stancl/tenancy v3.9)** with a **multi-database strategy**.

#### Implementation Status: **Active**

**Package**: `stancl/tenancy` v3.9.1
**Strategy**: Multi-database (each tenant = separate database)
**Tenant Model**: `App\Models\Tenancy\Tenant`

#### Database Structure

**Landlord Database** (central):
```sql
tenants
├── id (string, UUID) - Primary key
├── name - Company/tenant name
├── plan (trial|starter|professional|enterprise)
├── status (trial|active|past_due|suspended|cancelled)
├── trial_ends_at, subscription_ends_at - Billing dates
├── stripe_customer_id, stripe_subscription_id
├── data (JSON) - Flexible metadata
└── timestamps

domains
├── id, domain (unique)
├── tenant_id (FK → tenants)
└── timestamps
```

**Tenant Databases**:
Each tenant gets a database named `tenant{uuid}` containing:
- Users and authentication tables
- Module system tables (modules, blocks, fields, field_options, module_records)
- Relationships, workflows, automations
- All tenant-specific data

#### Tenant Model Features

**Status Management**:
- `isOnTrial()` - Check if in trial period
- `trialHasExpired()` - Check if trial expired
- `isActive()` - Check active subscription
- `isSuspended()` - Check suspended/past_due status
- `activate()`, `suspend()` - Change status

**Relationships**:
- `domains()` - hasMany Domain (supports multiple domains per tenant)
- Uses `HasDatabase` and `HasDomains` traits from Tenancy package

#### Tenancy Bootstrappers (Automatic)

When tenant context is initialized, these bootstrappers make Laravel features tenant-aware:
- `DatabaseTenancyBootstrapper` - Switches database connection
- `CacheTenancyBootstrapper` - Prefixes cache keys with tenant ID
- `FilesystemTenancyBootstrapper` - Isolates file storage per tenant
- `QueueTenancyBootstrapper` - Ensures queued jobs run in tenant context

#### Sample Tenants (Seeder)

Run `php artisan db:seed --class=TenantSeeder` to create:
- **acme-corp** (acme.localhost) - Active professional plan
- **startup-inc** (startup.localhost) - Trial with 14 days remaining
- **enterprise-co** (enterprise.localhost) - Active enterprise plan
- **suspended-biz** (suspended.localhost) - Past due subscription
- **expired-trial** (expired.localhost) - Expired trial

#### Key Considerations

**Data Isolation**:
- Each tenant database is completely isolated
- No shared data between tenants
- Module definitions stored per-tenant (each can customize)
- Global scopes not needed (database-level isolation)

**Performance**:
- Database connection switching adds minimal overhead
- Tenancy context cached per request
- Use database connection pooling in production

**Migrations**:
- Central migrations: Run against landlord database
- Tenant migrations: Run against all tenant databases
- Use `php artisan tenants:migrate` for tenant migrations

**Testing**:
- Always set tenant context in tests
- Use `tenancy()->initialize($tenant)` in tests
- Clean tenant databases between test runs

#### Architectural Approach

**Hexagonal Architecture + DDD**:
- Domain layer remains framework-agnostic
- Tenancy handled at infrastructure layer
- Repository implementations are tenant-aware
- Business logic doesn't reference tenancy directly

**Benefits**:
- Complete data isolation (highest security)
- Independent scaling per tenant
- Tenant-specific database optimizations possible
- Easy to migrate large tenants to dedicated servers
- Compliance-friendly (data residency requirements)

### Dynamic Module System

The module builder allows tenants to create custom entities and fields at runtime.

#### Database Structure
- **modules** - Module definitions (name, icon, settings)
- **blocks** - Logical groupings within modules (sections, tabs, accordions)
- **fields** - Individual fields with type, validation, and display settings
- **field_options** - Options for select/multiselect/radio fields
- **module_records** - JSON storage for dynamic module data
- **module_relationships** - Defines relationships between modules

#### Architecture Pattern: Hexagonal (Ports & Adapters) + DDD

**Domain Layer** (`app/Domain/Modules/`)
- **Entities**: Module, Block, Field, FieldOption - Pure business objects
- **Value Objects**: FieldType, BlockType, ModuleSettings, FieldSettings, ValidationRules
- **Repository Interfaces**: Define contracts (ports) for data access

**Infrastructure Layer** (`app/Infrastructure/Persistence/Eloquent/`)
- **Models**: Eloquent models for database interaction
- **Repositories**: Implementations (adapters) that map between Eloquent and Domain entities
- Example: `EloquentModuleRepository` implements `ModuleRepositoryInterface`

**Key Principles:**
- Domain entities are framework-agnostic (no Laravel dependencies)
- Repository pattern provides abstraction over data persistence
- Value objects ensure type safety and encapsulation
- Domain logic lives in entities, not in controllers or models

#### Field Types Supported
Text, Textarea, Number, Decimal, Email, Phone, URL, Select, Multiselect, Radio, Checkbox, Toggle, Date, DateTime, Time, Currency, Percent, Lookup (relationships), Formula, File, Image, Rich Text

#### Frontend Form Components

**Base FieldWrapper** (`resources/js/components/form/FieldWrapper.svelte`)
- Wraps all form inputs with consistent structure
- Includes label, description, help text, error messages, required indicator
- Responsive width support (25%, 50%, 75%, 100%)
- Accessibility features (aria-invalid, aria-describedby)

**Field-Specific Wrappers** (using shadcn-svelte components)
- `TextFieldWrapper` - Text inputs (text, email, phone, url, number)
- `TextareaFieldWrapper` - Multi-line text
- `SelectFieldWrapper` - Dropdown selection with options
- `CheckboxFieldWrapper` - Boolean checkbox
- `SwitchFieldWrapper` - Toggle switch

All field wrappers exported from `@/components/form` for easy imports.

### Workflow & Automation Engine

**Workflows:**
- Visual workflow builder for defining multi-step processes
- Support for conditions, branching, and parallel execution
- Trigger-based and manual workflow initiation

**Automations:**
- Event-driven automation system
- Triggers: record creation, updates, time-based, webhook events
- Actions: email notifications, field updates, API calls, custom actions

### SSR Support

SSR is configured and ready:
- Entry point: `resources/js/ssr.ts`
- Vite config includes SSR build setup
- Uses Svelte 5's hydration for optimal performance

### Database

Migrations in `database/migrations/`:
- Standard Laravel auth tables (users, password_reset_tokens, sessions)
- Cache and jobs tables
- Custom module tables (module_tables migration)

Use `php artisan migrate` to run migrations.

## Styling Approach

### Theme System
Three-mode appearance system (light/dark/system):
- Frontend: `useAppearance()` hook manages localStorage and DOM classes
- Backend: `HandleAppearance` middleware syncs cookie state
- Initial theme set server-side to prevent flash

### Tailwind Configuration
- Version: Tailwind CSS v4 (latest)
- Config: Uses Vite plugin (`@tailwindcss/vite`)
- UI Components: Follow shadcn-svelte patterns with class-variance-authority

### Adding shadcn-svelte Components
```bash
npx shadcn-svelte add [component]      # Add new component
npm run shadcn:update                  # Update all components
```

Configuration in `components.json`.

## Laravel Wayfinder

Laravel Wayfinder is integrated for type-safe routing:
- Installed: `laravel/wayfinder` + `@laravel/vite-plugin-wayfinder`
- Enabled with form variants in `vite.config.js`
- Provides TypeScript route helpers for Inertia navigation

## PHP Code Style

Laravel Pint enforces strict code quality rules:
- Preset: Laravel
- Strict types declarations required (`declare(strict_types=1)`)
- Final classes by default
- Strict comparisons enforced
- Ordered class elements (traits, constants, properties, methods)
- Global namespace imports for classes, constants, and functions

Always run `composer pint` before committing PHP code.

## Testing Configuration

PHPUnit configured with:
- SQLite in-memory database for tests
- Array cache and mail drivers
- Bcrypt rounds reduced to 4 for speed
- Queue connection set to sync

## Environment Setup

1. Copy `.env.example` to `.env`
2. Run `php artisan key:generate`
3. Configure database connection
4. Run `php artisan migrate`
5. Install dependencies: `composer install && npm install`

## Key Dependencies

### Frontend
- **Svelte 5**: Latest with runes API
- **Inertia.js 2**: Backend-frontend bridge
- **shadcn-svelte**: UI component system with bits-ui primitives
- **formsnap**: Form handling with validation
- **layerchart**: Charting library
- **svelte-sonner**: Toast notifications

### Backend
- **Laravel 12**: Framework
- **Inertia Laravel**: Server-side adapter
- **Laravel Wayfinder**: Type-safe routing
## Dynamic Form System

The application uses a database-driven dynamic form system where forms are generated from module definitions stored in the database.

### Form Architecture

**Database Structure**:
- `modules` - Top-level entities (e.g., Contacts, Leads)
- `blocks` - Sections within a module that group related fields
- `fields` - Individual form fields with type, validation, and display settings
- `field_options` - Options for select/radio/checkbox fields

**Form Components** (`resources/js/components/form/`):
- `FieldBase.svelte` - Base wrapper providing label, description, error display
- `TextField.svelte` - Text and email input fields
- `TextareaField.svelte` - Multi-line text input
- `SelectField.svelte` - Dropdown select with options

### Using Form Components

Each field component uses Svelte 5 snippet pattern to pass props to child inputs:

```svelte
<TextField
  label="First Name"
  name="first_name"
  description="Enter your first name"
  required={true}
  width={50}
  bind:value={formData.first_name}
/>

<SelectField
  label="Status"
  name="status"
  options={[
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
  ]}
  bind:value={formData.status}
/>
```

### Creating Dynamic Forms

Forms are automatically generated from module definitions:

```typescript
// Controller loads module with relationships
$module = ModuleModel::with(['blocks.fields.fieldOptions'])
  ->where('name', 'Contacts')
  ->first();

// Frontend renders fields dynamically based on field.type
{#each block.fields as field}
  {#if field.type === 'text'}
    <TextField {...field} bind:value={formData[field.api_name]} />
  {/if}
{/each}
```

### Demo Page

Visit `/demo/dynamic-form` to see the dynamic form system in action with the Test Form module that includes:
- Text fields (first_name, last_name)
- Email field
- Select field with options (status)
- Textarea field (bio)

### Seeding Test Data

Run `php artisan tenants:seed --tenants=acme-corp` to seed the Test Form module with sample fields.
