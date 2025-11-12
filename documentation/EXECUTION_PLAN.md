# VrtxCRM Execution Plan

**Last Updated:** 2025-11-11
**Strategy:** Multi-Database Tenancy (Each tenant = Separate PostgreSQL database)
**Status:** Phase 1, Sprint 1-2 - In Progress

---

## 🏗️ **MULTI-TENANCY ARCHITECTURE CONFIRMED**

### **Database Strategy: Multi-Database (Separate Databases per Tenant)**

**Why Multi-Database:**
- ✅ **Complete data isolation** - Highest security, no cross-tenant data leaks possible
- ✅ **Independent scaling** - Large tenants can be moved to dedicated servers
- ✅ **Tenant-specific optimizations** - Each database can be tuned independently
- ✅ **Compliance-friendly** - Easy data residency (EU tenant data stays in EU)
- ✅ **Backup/restore per tenant** - Granular control
- ✅ **Performance isolation** - One tenant's load doesn't affect others

**Database Naming Convention:**
- Landlord (Central) DB: `vrtx` (configured in .env)
- Tenant DBs: `tenant{uuid}` (e.g., `tenant550e8400-e29b-41d4-a716-446655440000`)

**How It Works:**
1. Request comes in with subdomain: `acme.vrtxcrm.com`
2. Middleware identifies tenant from domain lookup in landlord DB
3. Laravel switches database connection to `tenant{uuid}`
4. All queries execute against tenant's isolated database
5. Bootstrappers ensure cache, files, queues are also tenant-scoped

---

## 📋 **PHASE 1: MVP - CORE PLATFORM** (16-20 weeks)

### **SPRINT 1-2: Multi-Tenancy Foundation** (2 weeks) - 🟡 IN PROGRESS

#### ✅ **Week 1: Days 1-3 - COMPLETED**
- [x] Install stancl/tenancy v3.9.1
- [x] Create Tenant model (app/Models/Tenancy/Tenant.php)
- [x] Create Domain model (app/Models/Tenancy/Domain.php)
- [x] Create TenantSetting model (app/Models/Tenancy/TenantSetting.php)
- [x] Create landlord migrations (tenants, domains, tenant_settings)
- [x] Configure tenancy.php (bootstrappers, multi-database)
- [x] Start PostgreSQL via Docker
- [x] Run landlord migrations

**Testing:** ✅ Manual verification of migrations and Docker setup

---

#### 🔄 **Week 1: Days 4-5 - CURRENT**

**Tasks:**
- [ ] Configure tenant routing (subdomain-based)
- [ ] Create TenantService for provisioning
- [ ] Create first test tenant
- [ ] Write tenant isolation tests
- [ ] Create TenantSeeder with sample tenants

**Files to Create:**
- `app/Services/TenantService.php` - Core provisioning logic
- `database/seeders/TenantSeeder.php` - Seed sample tenants
- `tests/Feature/Tenancy/TenantCreationTest.php` - Test tenant provisioning
- `tests/Feature/Tenancy/TenantIsolationTest.php` - Test data isolation
- Update `routes/tenant.php` - Tenant-specific routes

**Testing Strategy:**
- ✅ Test tenant creation (creates database, runs migrations)
- ✅ Test domain assignment
- ✅ Test database isolation (data in tenant1 not visible in tenant2)
- ✅ Test cache isolation
- ✅ Test file storage isolation
- ✅ Test queue isolation

---

#### 📅 **Week 2: Days 1-3**

**Tasks:**
- [ ] Build SuperAdmin middleware (access control)
- [ ] Create super admin routes
- [ ] Create TenantController (CRUD for tenants)
- [ ] Build tenant management UI (list, create, view, edit)
- [ ] Add tenant status controls (activate, suspend, delete)

**Files to Create:**
- `app/Http/Middleware/SuperAdmin.php`
- `app/Http/Controllers/Admin/TenantController.php`
- `resources/js/pages/admin/tenants/Index.svelte`
- `resources/js/pages/admin/tenants/Create.svelte`
- `resources/js/pages/admin/tenants/Show.svelte`
- `routes/admin.php` - Super admin routes

**Testing:**
- ✅ Test super admin access control
- ✅ Test tenant CRUD operations
- ✅ Test tenant status changes
- ✅ Test tenant deletion (cascade deletes database)

---

#### 📅 **Week 2: Days 4-5**

**Tasks:**
- [ ] Create tenant settings UI
- [ ] Implement branding settings (logo, colors)
- [ ] Add usage metrics dashboard
- [ ] Update CLAUDE.md documentation

**Files to Create:**
- `resources/js/pages/admin/tenants/Settings.svelte`
- `app/Http/Controllers/Admin/TenantSettingsController.php`

**Testing:**
- ✅ Test tenant settings CRUD
- ✅ Test branding configuration

---

### **SPRINT 3-4: Dynamic Module System - Database & Domain** (2 weeks)

#### 📅 **Week 3: Days 1-5**

**Tasks:**
- [ ] Create module system migrations (6 tables)
- [ ] Enhance domain layer (events, aggregates)
- [ ] Create ModuleService, FieldService, RecordService
- [ ] Add advanced field types (Signature, Location, etc.)
- [ ] Implement ArkType validation integration

**Database Migrations (Tenant DBs):**
- `database/migrations/tenant/create_modules_table.php`
- `database/migrations/tenant/create_blocks_table.php`
- `database/migrations/tenant/create_fields_table.php`
- `database/migrations/tenant/create_field_options_table.php`
- `database/migrations/tenant/create_module_records_table.php`
- `database/migrations/tenant/create_module_relationships_table.php`

**Testing:**
- ✅ Test module creation within tenant context
- ✅ Test module isolation between tenants
- ✅ Test field validation rules
- ✅ Test relationship creation

---

#### 📅 **Week 4: Days 1-5**

**Tasks:**
- [ ] Install ArkType for runtime validation
- [ ] Create ValidationService (ArkType schema generator)
- [ ] Add domain events (ModuleCreated, FieldAdded, etc.)
- [ ] Implement aggregate root pattern for Module
- [ ] Create ModuleSeeder (Contacts, Leads, Deals)

**Testing:**
- ✅ Test ArkType validation schemas
- ✅ Test domain events firing
- ✅ Test aggregate business logic

---

### **SPRINT 5-6: Complete Form Component Library** (2 weeks)

#### 📅 **Week 5: Days 1-5**

**Tasks:**
- [ ] Install form dependencies (@dnd-kit, date-fns, etc.)
- [ ] Create 16 missing field components
- [ ] Build FormBuilder component (dynamic renderer)
- [ ] Implement FieldRenderer with type switching
- [ ] Add conditional logic handler

**Field Components to Create:**
- `CheckboxField.svelte`, `RadioField.svelte`, `SwitchField.svelte`
- `DateField.svelte`, `DateTimeField.svelte`, `TimeField.svelte`
- `CurrencyField.svelte`, `PercentField.svelte`
- `MultiselectField.svelte`, `LookupField.svelte`
- `FileField.svelte`, `ImageField.svelte`
- `SignatureField.svelte`, `LocationField.svelte`
- `RepeaterField.svelte`, `JsonField.svelte`, `FormulaField.svelte`

**Testing:**
- ✅ Test each field component renders correctly
- ✅ Test field validation
- ✅ Test conditional logic (show/hide)
- ✅ Test form submission with all field types

---

#### 📅 **Week 6: Days 1-5**

**Tasks:**
- [ ] Build FormLayout component (sections, tabs, columns)
- [ ] Add inline editing support
- [ ] Create BulkEdit component
- [ ] Implement form auto-save (localStorage)
- [ ] Add form error handling

**Testing:**
- ✅ Test form layouts render correctly
- ✅ Test inline editing
- ✅ Test bulk edit operations
- ✅ Test auto-save functionality

---

### **SPRINT 7-8: Module Builder UI** (2 weeks)

#### 📅 **Week 7: Days 1-5**

**Tasks:**
- [ ] Install @dnd-kit/core and @dnd-kit/sortable
- [ ] Create ModuleController (CRUD)
- [ ] Build module list page with search
- [ ] Create module creation wizard
- [ ] Add module settings page

**Testing:**
- ✅ Test module CRUD operations
- ✅ Test module activation/deactivation
- ✅ Test module cloning

---

#### 📅 **Week 8: Days 1-5**

**Tasks:**
- [ ] Build drag-drop field designer
- [ ] Create field library panel
- [ ] Add field configuration drawer
- [ ] Implement validation rule builder UI
- [ ] Create relationship configurator

**Testing:**
- ✅ Test drag-drop functionality
- ✅ Test field configuration saves correctly
- ✅ Test validation rules apply

---

### **SPRINT 9-10: Record Management System** (2 weeks)

#### 📅 **Week 9: Days 1-5**

**Tasks:**
- [ ] Install @tanstack/svelte-table
- [ ] Create RecordController (generic CRUD)
- [ ] Build DataTable component with features
- [ ] Add filtering, sorting, pagination
- [ ] Implement saved views

**Testing:**
- ✅ Test record CRUD for any module
- ✅ Test filtering and sorting
- ✅ Test pagination
- ✅ Test saved views persistence

---

#### 📅 **Week 10: Days 1-5**

**Tasks:**
- [ ] Create detail view layout
- [ ] Build dynamic edit forms
- [ ] Add record versioning
- [ ] Implement bulk operations
- [ ] Add CSV/Excel export

**Testing:**
- ✅ Test detail view displays all fields
- ✅ Test record updates with validation
- ✅ Test bulk operations
- ✅ Test export functionality

---

### **SPRINT 11-12: Core CRM Modules** (2 weeks)

#### 📅 **Week 11: Days 1-5**

**Tasks:**
- [ ] Seed Contacts module (15 fields)
- [ ] Seed Accounts module (12 fields)
- [ ] Seed Leads module (10 fields)
- [ ] Seed Deals module (12 fields)
- [ ] Create polymorphic tags system

**Testing:**
- ✅ Test all 4 CRM modules work
- ✅ Test relationships between modules
- ✅ Test tagging functionality

---

#### 📅 **Week 12: Days 1-5**

**Tasks:**
- [ ] Implement duplicate detection
- [ ] Build contact merge UI
- [ ] Add CSV import with field mapping
- [ ] Create vCard export
- [ ] Implement lead scoring

**Testing:**
- ✅ Test duplicate detection accuracy
- ✅ Test contact merging
- ✅ Test CSV import with validation
- ✅ Test lead scoring calculation

---

### **SPRINT 13-14: Pipeline & Sales Management** (2 weeks)

#### 📅 **Week 13: Days 1-5**

**Tasks:**
- [ ] Create pipelines table and model
- [ ] Create pipeline_stages table
- [ ] Build Pipeline CRUD
- [ ] Create kanban board component
- [ ] Add drag-drop stage movement

**Testing:**
- ✅ Test multiple pipelines per tenant
- ✅ Test drag-drop updates deal stage
- ✅ Test stage automation triggers

---

#### 📅 **Week 14: Days 1-5**

**Tasks:**
- [ ] Build forecasting dashboard
- [ ] Add revenue calculations
- [ ] Create win rate analytics
- [ ] Implement deal velocity metrics
- [ ] Add sales funnel visualization

**Testing:**
- ✅ Test forecast accuracy
- ✅ Test analytics calculations
- ✅ Test funnel visualization

---

### **SPRINT 15-16: Task & Activity System** (2 weeks)

#### 📅 **Week 15: Days 1-5**

**Tasks:**
- [ ] Create activities table (polymorphic)
- [ ] Create tasks table
- [ ] Build timeline component
- [ ] Add activity creation forms
- [ ] Implement Tiptap for rich text notes

**Testing:**
- ✅ Test activities attach to any record
- ✅ Test task creation and completion
- ✅ Test timeline displays correctly

---

#### 📅 **Week 16: Days 1-5**

**Tasks:**
- [ ] Build calendar view component
- [ ] Add Google Calendar sync (OAuth)
- [ ] Add Outlook Calendar sync (OAuth)
- [ ] Implement recurring tasks
- [ ] Add task reminders

**Testing:**
- ✅ Test calendar sync both directions
- ✅ Test recurring task generation
- ✅ Test reminder notifications

---

### **SPRINT 17-18: Basic Automation & Workflows** (2 weeks)

#### 📅 **Week 17: Days 1-5**

**Tasks:**
- [ ] Create workflows table
- [ ] Create workflow_nodes, workflow_edges tables
- [ ] Build workflow execution engine
- [ ] Implement trigger types (RecordCreated, etc.)
- [ ] Create action handlers (SendEmail, UpdateField, etc.)

**Testing:**
- ✅ Test workflow triggers fire correctly
- ✅ Test workflow actions execute
- ✅ Test workflow with conditions

---

#### 📅 **Week 18: Days 1-5**

**Tasks:**
- [ ] Build linear workflow builder UI
- [ ] Create email templates system
- [ ] Implement email sending service
- [ ] Add workflow execution logs
- [ ] Create workflow testing/debugging

**Testing:**
- ✅ Test workflow creation via UI
- ✅ Test email sending works
- ✅ Test workflow logs track execution

---

### **SPRINT 19-20: Security, Permissions & Polish** (2 weeks)

#### 📅 **Week 19: Days 1-5**

**Tasks:**
- [ ] Create roles and permissions tables
- [ ] Implement RBAC middleware
- [ ] Build role management UI
- [ ] Add module-level permissions
- [ ] Implement field-level permissions

**Testing:**
- ✅ Test role-based access control
- ✅ Test permission gates work
- ✅ Test field visibility by role

---

#### 📅 **Week 20: Days 1-5**

**Tasks:**
- [ ] Add MFA (TOTP, SMS)
- [ ] Implement SSO (Google, Microsoft)
- [ ] Add GDPR compliance tools
- [ ] Implement data export/anonymization
- [ ] Add audit logging

**Testing:**
- ✅ Test MFA enrollment and login
- ✅ Test SSO authentication
- ✅ Test GDPR data export
- ✅ Test audit logs capture changes

---

## 📊 **TESTING STRATEGY**

### **Testing Pyramid:**
- **Unit Tests** (60%): Domain entities, value objects, services
- **Feature Tests** (30%): API endpoints, controllers, full flows
- **Browser Tests** (10%): Critical user paths via Playwright

### **Continuous Testing:**
- ✅ Write tests alongside features (TDD approach)
- ✅ Run tests on every commit
- ✅ Maintain 80%+ coverage minimum
- ✅ Test multi-tenancy isolation in every feature

### **Key Test Suites:**
1. **Tenancy Tests** - Database isolation, cache isolation, file isolation
2. **Module Tests** - CRUD, validation, relationships
3. **Record Tests** - Dynamic CRUD, field types, versioning
4. **Permission Tests** - RBAC, field-level, record-level
5. **Workflow Tests** - Triggers, actions, conditions
6. **Integration Tests** - End-to-end user flows

---

## 🎯 **SUCCESS CRITERIA - PHASE 1 MVP**

### **Functional Requirements:**
- ✅ 10+ tenants running concurrently without interference
- ✅ 1,000+ records per module per tenant
- ✅ 20+ custom modules created across all tenants
- ✅ All 20 field types working correctly
- ✅ Multi-tenant isolation verified (database, cache, files, queues)

### **Non-Functional Requirements:**
- ✅ 80%+ test coverage (PHPUnit + Playwright)
- ✅ <2s page load times (p95)
- ✅ <500ms API response times (p95)
- ✅ Zero critical security vulnerabilities
- ✅ Mobile responsive (all pages)

### **Documentation:**
- ✅ API documentation (for internal use)
- ✅ User guide (module builder, record management)
- ✅ Admin guide (tenant management)
- ✅ Developer guide (adding features)

---

## 🚀 **DEPLOYMENT READINESS - PHASE 1**

### **Infrastructure:**
- [ ] Production PostgreSQL cluster (multi-region)
- [ ] Redis cluster for caching
- [ ] S3 for file storage
- [ ] CDN for assets
- [ ] Application load balancer

### **Monitoring:**
- [ ] Error tracking (Sentry)
- [ ] Performance monitoring (New Relic)
- [ ] Uptime monitoring
- [ ] Log aggregation

### **Security:**
- [ ] SSL certificates (Let's Encrypt)
- [ ] Rate limiting configured
- [ ] CORS policies set
- [ ] Security headers enabled
- [ ] Database backups automated

---

## 📈 **PROGRESS TRACKING**

### **Current Sprint Progress:**
- **Sprint 1-2 (Multi-Tenancy):** 50% complete (Week 1: Days 1-3 done)
- **Overall Phase 1 Progress:** 3% complete

### **Velocity Tracking:**
- Average story points per week: TBD (will track after Sprint 1-2)
- Estimated completion of Phase 1: 16-20 weeks from start

### **Risk Register:**
| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Multi-database performance issues | High | Medium | Implement caching, connection pooling, monitoring |
| Tenant data leakage | Critical | Low | Comprehensive isolation tests, security audit |
| Module builder complexity | Medium | Medium | Iterative development, user testing |
| Calendar sync API limits | Medium | High | Implement rate limiting, queueing, fallback |

---

## 📝 **NOTES & DECISIONS**

### **Architecture Decisions:**
1. **Multi-Database over Single-Database** - Chosen for security, scalability, compliance
2. **ArkType over Zod** - Modern, faster, better TypeScript integration
3. **Svelte 5 Runes** - Latest reactive patterns, better performance
4. **PostgreSQL over MySQL** - Better JSON support, full-text search, PostGIS for location
5. **Inertia.js over REST API** - Simpler architecture for monolith, still can add API later

### **Technology Stack Confirmed:**
- **Backend:** Laravel 12, PHP 8.2+, PostgreSQL 17
- **Frontend:** Svelte 5, TypeScript (strict), Inertia.js 2
- **UI:** Tailwind CSS v4, shadcn-svelte
- **Validation:** ArkType (backend + frontend)
- **Tenancy:** stancl/tenancy v3.9 (multi-database)
- **Testing:** PHPUnit, Playwright
- **Infrastructure:** Docker, PostgreSQL, Redis, S3

---

**Next Update:** After Sprint 1-2 completion (end of Week 2)
