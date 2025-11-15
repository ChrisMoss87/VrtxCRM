# VrtxCRM Documentation

**Last Updated**: 2025-11-14

This folder contains all project documentation.

---

## Primary Documentation

### 📊 **[MODULE_BUILDER_PLAN.md](./MODULE_BUILDER_PLAN.md)** ⭐ **NEW - TOP PRIORITY**
**Complete Module Builder system - Foundation of VrtxCRM**

Comprehensive execution plan covering:
- Field configuration UI with all field types
- Preview and testing mode
- Module publishing workflow
- Integration with DataTable for CRUD
- Advanced features (relationships, formulas, conditional logic)
- Testing and documentation

**Estimated**: ~66 hours (8-9 days)

---

### 📋 **[EXECUTION_PLAN.md](./EXECUTION_PLAN.md)**
**DataTable system completion plan**

Contains:
- DataTable feature completion
- Detailed tasks with time estimates
- Success criteria
- Performance targets
- Integration points

---

## Additional Documentation

### DataTable System
- **[DATATABLE_ARCHITECTURE.md](./DATATABLE_ARCHITECTURE.md)** - DataTable component architecture (if exists)

### Quick Reference
- **[QUICK_START.md](./QUICK_START.md)** - Development setup guide
- **[TEST_CREDENTIALS.md](./TEST_CREDENTIALS.md)** - Login credentials for testing
- **[TESTING.md](./TESTING.md)** - Testing strategy

---

## Development Quick Reference

### Start Development
```bash
composer dev  # Starts Laravel + Queue + Pail + Vite
```

### Run Tests
```bash
composer test                 # PHPUnit
npm run test:browser          # Playwright
```

### Code Quality
```bash
composer pint                 # PHP formatting
npm run format                # JS/Svelte formatting
```

### Access Points
- Central: `http://vrtxcrm.local`
- Tenant (Acme): `http://acme.vrtxcrm.local`
  - Email: `admin@test.com`
  - Password: `password`

---

## Current Status (2025-11-14)

### ✅ Completed
- Multi-tenant architecture (multi-database strategy)
- Authentication system with tenant context
- Dynamic module system (backend)
- Module Builder UI with drag-and-drop
- DataTable with filters, sorting, pagination
- User preferences persistence

### 🔄 In Progress
- **DataTable System Completion** (See EXECUTION_PLAN.md)
  - Row selection
  - Bulk operations
  - Inline editing
  - Export functionality

### 📋 Next Up
1. Complete DataTable (4-5 days)
2. Relationships & Lookup fields
3. Workflows & Automation
4. Reporting & Analytics

---

## Architecture

**Pattern**: Hexagonal Architecture (Ports & Adapters) + Domain-Driven Design

**Structure**:
```
app/Domain/              - Pure business logic (entities, value objects)
app/Infrastructure/      - External concerns (DB, APIs, files)
app/Services/            - Application services (orchestration)
app/Http/Controllers/    - HTTP layer (thin controllers)
```

**Stack**:
- Backend: Laravel 12 + PHP 8.2 + PostgreSQL 17
- Frontend: Svelte 5 (Runes) + TypeScript + Inertia.js 2
- UI: Tailwind CSS v4 + shadcn-svelte
- Tenancy: stancl/tenancy v3.9 (multi-database)

---

## Notes

- All sprint documentation has been archived/removed
- **EXECUTION_PLAN.md** is the only active planning document
- Focus is on completing one feature at a time
- Tests are required before marking features complete
