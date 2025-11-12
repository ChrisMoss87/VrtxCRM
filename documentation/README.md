# VrtxCRM Documentation

This folder contains all project documentation, guides, and sprint summaries.

## Quick Start

- **[QUICK_START.md](./QUICK_START.md)** - Getting started with VrtxCRM development
- **[TEST_CREDENTIALS.md](./TEST_CREDENTIALS.md)** - Login credentials for testing

## Current Session Work

- **[SESSION_SUMMARY.md](./SESSION_SUMMARY.md)** - Latest session: Multi-tenant authentication fix
- **[MULTI_TENANT_AUTH_FIXED.md](./MULTI_TENANT_AUTH_FIXED.md)** - Complete authentication fix guide
- **[TENANT_IDENTIFICATION_FIXED.md](./TENANT_IDENTIFICATION_FIXED.md)** - Tenant identification solution
- **[NGINX_SETUP.md](./NGINX_SETUP.md)** - Nginx multi-tenant configuration
- **[DOMAINS_CONFIGURED.md](./DOMAINS_CONFIGURED.md)** - Domain configuration status

## Architecture & Planning

- **[ARCHITECTURE_OVERVIEW.md](./ARCHITECTURE_OVERVIEW.md)** - System architecture overview
- **[MODULE_BUILDER_PLAN.md](./MODULE_BUILDER_PLAN.md)** - Dynamic module builder design
- **[SIMPLE_MULTITENANCY.md](./SIMPLE_MULTITENANCY.md)** - Multi-tenancy implementation guide
- **[EXECUTION_PLAN.md](./EXECUTION_PLAN.md)** - Development execution plan

## Sprint Summaries

- **[SPRINT_3-4_COMPLETE.md](./SPRINT_3-4_COMPLETE.md)** - Dynamic Module System (completed)
- **[SPRINT_1-2_REVIEW.md](./SPRINT_1-2_REVIEW.md)** - Foundation & UI (completed)
- **[PROGRESS_SUMMARY.md](./PROGRESS_SUMMARY.md)** - Overall project progress

## Testing

- **[TESTING.md](./TESTING.md)** - Testing strategy and guidelines
- **[PLAYWRIGHT_TESTS_READY.md](./PLAYWRIGHT_TESTS_READY.md)** - Playwright setup quick start
- **[SETUP_TESTS.md](./SETUP_TESTS.md)** - Detailed test setup guide
- **[MCP_PLAYWRIGHT_SETUP.md](./MCP_PLAYWRIGHT_SETUP.md)** - MCP Playwright integration

## Troubleshooting & Fixes

- **[FIX_NOW.md](./FIX_NOW.md)** - Critical issues and fixes
- **[LOGIN_FIX.md](./LOGIN_FIX.md)** - Login-related fixes
- **[RESET_STEPS.md](./RESET_STEPS.md)** - Reset and cleanup procedures
- **[README_CURRENT_STATE.md](./README_CURRENT_STATE.md)** - Current project state

## Project Structure

```
VrtxCRM/
├── documentation/          # This folder - all project docs
├── CLAUDE.md              # AI assistant instructions (root level)
├── app/
│   ├── Domain/            # DDD domain layer (business logic)
│   ├── Infrastructure/    # Data persistence, external services
│   └── Http/              # Controllers, middleware
├── database/
│   ├── migrations/        # Database migrations
│   │   ├── tenant/        # Tenant-specific migrations
│   │   └── [others]       # Landlord/central migrations
│   └── seeders/           # Database seeders
├── resources/js/
│   ├── pages/             # Inertia.js pages (Svelte)
│   ├── components/        # Reusable Svelte components
│   ├── layouts/           # Page layouts
│   └── lib/               # Utilities
├── routes/
│   ├── tenant.php         # Tenant-specific routes (with auth)
│   ├── web.php            # Central/public routes
│   ├── auth.php           # Authentication routes (loaded in tenant.php)
│   └── settings.php       # Settings routes (loaded in tenant.php)
└── tests/
    ├── Unit/              # Unit tests
    ├── Feature/           # Feature tests
    └── browser/           # Playwright E2E tests
```

## Current Status (2025-11-12)

✅ **Multi-tenant authentication working**
- Tenant identification via subdomain
- Login authenticates against tenant database
- Session persistence working
- Protected routes accessible

✅ **Testing**
- 15/18 Playwright comprehensive tests passing
- 3/3 basic login tests passing
- Core functionality verified

✅ **Sprint 3-4 Complete**
- Dynamic module system backend
- Database migrations for modules, blocks, fields
- Domain-driven architecture
- Repository pattern implementation

## Next Steps

1. Fix remaining 3 Playwright test edge cases (optional)
2. Update tenant seeder for correct domain format
3. Begin Sprint 5: Dynamic module frontend
4. Build module builder UI components

## Development Commands

```bash
# Start all services
composer dev

# Or individually:
php artisan serve              # Laravel server
npm run dev                    # Vite dev server
php artisan queue:listen       # Queue worker
php artisan pail               # Log viewer

# Testing
php artisan test                           # PHPUnit
npx playwright test                        # E2E tests
npx playwright test --ui                   # Interactive mode

# Code quality
composer pint                              # PHP formatting
npm run lint                               # ESLint
npm run format                             # Prettier
```

## Access Points

- **Central domain**: http://vrtxcrm.local
- **Tenant (Acme)**: http://acme.vrtxcrm.local
  - Email: admin@test.com
  - Password: password

## Documentation Organization

All documentation has been moved to this folder to keep the project root clean. The only markdown file at root level is `CLAUDE.md`, which contains instructions for the AI assistant.
