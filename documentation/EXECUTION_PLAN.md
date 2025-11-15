# VrtxCRM Development Execution Plan

**Last Updated**: 2025-11-15
**Status**: Active Development

---

## 📋 Active Plans

### 📊 [MODULE_BUILDER_PLAN.md](./MODULE_BUILDER_PLAN.md) ⭐ **PRIORITY**
**Complete Module Builder system and integrate with DataTable**

Comprehensive plan for finishing the Module Builder, which is the foundation of VrtxCRM's dynamic module system. Covers field configuration, preview mode, publishing workflow, and full integration with DataTable CRUD operations.

**Estimated Time**: ~66 hours (8-9 days)
**Status**: Planning Complete - Ready to Start

---

### 📋 DataTable System Completion (This Document)
**Complete DataTable with remaining features**

---

## Overview

VrtxCRM is a multi-tenant CRM platform built with Laravel 12 + Svelte 5. Current priorities are completing the DataTable component system and the Module Builder.

### Technology Stack
- **Backend**: Laravel 12, PHP 8.2+, PostgreSQL 17
- **Frontend**: Svelte 5 (Runes), TypeScript (strict mode), Inertia.js 2
- **UI**: Tailwind CSS v4, shadcn-svelte components
- **Tenancy**: stancl/tenancy v3.9 (multi-database strategy)
- **Testing**: PHPUnit (backend), Playwright (browser)

---

## Current Task: DataTable System Completion

### Goal
Build a production-ready DataTable component that can display, filter, sort, and manage records for any dynamic module in the system.

---

## Phase 1: Backend Completion

### 1.1 Repository Layer ✅
**Priority**: High
**Estimated Time**: 4 hours
**Status**: COMPLETE

**Tasks**:
- [x] Create `ModuleRecordRepositoryInterface`
- [x] Implement `EloquentModuleRecordRepository`
- [x] Add query builder methods for filtering
- [x] Add sorting and pagination methods
- [x] Handle dynamic field queries
- [x] Wire up dependency injection

**Files**:
- `app/Domain/Modules/Repositories/ModuleRecordRepositoryInterface.php` ✅
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentModuleRecordRepository.php` ✅
- `app/Providers/ModuleServiceProvider.php` ✅
- `app/Domain/Modules/Entities/ModuleRecord.php` ✅

**Acceptance Criteria**:
- ✅ Repository follows hexagonal architecture
- ✅ Proper entity-to-model mapping
- ✅ Supports complex filter queries (15+ operators)
- ✅ Handles all field types correctly

---

### 1.2 Service Layer Refactoring ✅
**Priority**: High
**Estimated Time**: 3 hours
**Status**: COMPLETE

**Tasks**:
- [x] Refactor `RecordService` to use repository
- [x] Remove direct Eloquent usage
- [x] Add proper validation for dynamic fields
- [x] Implement field type conversions
- [x] Add transaction support

**Files**:
- `app/Services/RecordService.php` ✅

**Acceptance Criteria**:
- ✅ No direct Eloquent model usage
- ✅ All operations use domain entities
- ✅ Proper error handling
- ✅ Transaction rollback on failures
- ✅ Field type conversion (int, float, bool, array)

---

### 1.3 Bulk Operations API ✅
**Priority**: Medium
**Estimated Time**: 2 hours
**Status**: COMPLETE

**Tasks**:
- [x] Add `bulkDelete()` endpoint
- [x] Add `bulkUpdate()` endpoint
- [x] Add request validation
- [x] Refactor controller to use RecordService

**Files**:
- `app/Http/Controllers/Api/ModuleRecordController.php` ✅
- `routes/tenant.php` ✅

**Acceptance Criteria**:
- ✅ Can delete multiple records at once
- ✅ Can update multiple records at once
- ✅ Validates all record IDs exist
- ✅ Returns proper error responses
- ✅ Uses domain entities and service layer

---

### 1.4 Export Functionality ⬜
**Priority**: Low
**Estimated Time**: 3 hours

**Tasks**:
- [ ] Install `maatwebsite/excel` package
- [ ] Create `ExportService`
- [ ] Add `export()` endpoint
- [ ] Support CSV format
- [ ] Support Excel format
- [ ] Apply filters to export

**Files**:
- `app/Services/ExportService.php`
- `app/Http/Controllers/Api/ModuleRecordController.php`

**Acceptance Criteria**:
- Can export filtered data
- CSV format works
- Excel format works
- Handles large datasets (10k+ records)

---

## Phase 2: Frontend Completion

### 2.1 Row Selection ✅
**Priority**: High
**Estimated Time**: 2 hours
**Status**: COMPLETE

**Tasks**:
- [x] Add checkbox column to DataTable
- [x] Implement single row selection
- [x] Implement select all/none
- [x] Store selection state

**Files**:
- `resources/js/components/datatable/DataTable.svelte` ✅
- `resources/js/components/datatable/DataTableHeader.svelte` ✅
- `resources/js/components/datatable/DataTableBody.svelte` ✅

**Acceptance Criteria**:
- ✅ Single click selects row
- ✅ Select all checkbox in header
- ✅ Selection state is reactive
- ✅ Indeterminate state for partial selection

---

### 2.2 Bulk Actions UI ✅
**Priority**: High
**Estimated Time**: 2 hours
**Status**: COMPLETE

**Tasks**:
- [x] Add bulk action toolbar
- [x] Add bulk delete button
- [x] Add confirmation dialog
- [x] Show success/error toasts
- [x] Refresh table after action
- [x] Integrate with backend API

**Files**:
- `resources/js/components/datatable/DataTableToolbar.svelte` ✅

**Acceptance Criteria**:
- ✅ Toolbar appears when rows selected
- ✅ Delete requires confirmation (AlertDialog)
- ✅ Success/error feedback shown (toast)
- ✅ Table refreshes automatically
- ✅ Selection cleared after delete

---

### 2.3 Inline Editing ⬜
**Priority**: Medium
**Estimated Time**: 4 hours

**Tasks**:
- [ ] Make cells editable on double-click
- [ ] Show input field inline
- [ ] Save on blur or Enter
- [ ] Cancel on Escape
- [ ] Show validation errors

**Files**:
- `resources/js/components/datatable/EditableCell.svelte` (new)
- `resources/js/components/datatable/DataTableBody.svelte`

**Acceptance Criteria**:
- Double-click activates edit mode
- Enter/blur saves changes
- Escape cancels editing
- API errors shown inline

---

### 2.4 Export Button ⬜
**Priority**: Low
**Estimated Time**: 1 hour

**Tasks**:
- [ ] Add export button to toolbar
- [ ] Add format selector (CSV/Excel)
- [ ] Show download progress
- [ ] Trigger file download

**Files**:
- `resources/js/components/datatable/DataTableToolbar.svelte`

**Acceptance Criteria**:
- Export button in toolbar
- User can choose format
- Downloads file correctly
- Shows progress indicator

---

### 2.5 Additional Filters ⬜
**Priority**: Medium
**Estimated Time**: 3 hours

**Tasks**:
- [ ] Create `LookupFilter` component
- [ ] Create `MultiSelectFilter` component
- [ ] Add filter group UI (AND/OR)
- [ ] Add save filter preset
- [ ] Add load filter preset

**Files**:
- `resources/js/components/datatable/filters/LookupFilter.svelte` (new)
- `resources/js/components/datatable/filters/MultiSelectFilter.svelte` (new)
- `resources/js/components/datatable/filters/FilterGroups.svelte` (new)

**Acceptance Criteria**:
- Lookup field can search related records
- Multi-select shows checkboxes
- Can combine filters with AND/OR
- Can save/load filter combinations

---

## Phase 3: Integration & Testing

### 3.1 Module Record Views ⬜
**Priority**: High
**Estimated Time**: 4 hours

**Tasks**:
- [ ] Create Index page with DataTable
- [ ] Create Create page with dynamic form
- [ ] Create Edit page with validation
- [ ] Create Show page with related records
- [ ] Add delete confirmation dialog
- [ ] Add toast notifications

**Files**:
- `resources/js/pages/modules/{moduleApiName}/Index.svelte`
- `resources/js/pages/modules/{moduleApiName}/Create.svelte`
- `resources/js/pages/modules/{moduleApiName}/Edit.svelte`
- `resources/js/pages/modules/{moduleApiName}/Show.svelte`

**Acceptance Criteria**:
- All CRUD operations work
- Validation errors displayed
- Related records shown
- Toast notifications on actions

---

### 3.2 Browser Tests ⬜
**Priority**: High
**Estimated Time**: 3 hours

**Tasks**:
- [ ] Write DataTable interaction tests
- [ ] Write filter tests
- [ ] Write sort/pagination tests
- [ ] Write row selection tests
- [ ] Write bulk action tests
- [ ] Write CRUD operation tests

**Files**:
- `tests/browser/datatable.spec.ts`
- `tests/browser/module-records.spec.ts`

**Acceptance Criteria**:
- All user interactions tested
- Filters work correctly
- Pagination navigates pages
- Selection and bulk delete work
- CRUD operations complete successfully

---

### 3.3 Performance Optimization ⬜
**Priority**: Medium
**Estimated Time**: 2 hours

**Tasks**:
- [ ] Add debounce to search input (300ms)
- [ ] Add debounce to filter inputs (500ms)
- [ ] Optimize query with proper indexes
- [ ] Add loading states
- [ ] Add skeleton loaders

**Files**:
- `resources/js/components/datatable/DataTableToolbar.svelte`
- `resources/js/components/datatable/filters/*.svelte`
- Database migrations for indexes

**Acceptance Criteria**:
- Search doesn't trigger on every keystroke
- Filters debounced properly
- Database queries optimized
- Loading states shown
- No layout shift

---

## Success Criteria

The DataTable system is complete when:

1. ✅ User can view records in a paginated table
2. ✅ User can filter records by any field type
3. ✅ User can sort by any column
4. ✅ User can customize column visibility and order
5. ✅ User preferences persist across sessions
6. ✅ User can select and bulk delete records
7. ⬜ User can export filtered data to CSV/Excel
8. ⬜ User can inline-edit simple fields
9. ⬜ All CRUD operations have browser tests
10. ⬜ Performance targets are met (<500ms load)

---

## Timeline Estimate

| Phase | Tasks | Estimated Time |
|-------|-------|----------------|
| **Phase 1: Backend** | Repository, Service, Bulk Ops, Export | 12 hours |
| **Phase 2: Frontend** | Selection, Bulk UI, Inline Edit, Export, Filters | 12 hours |
| **Phase 3: Integration** | Views, Tests, Performance | 9 hours |
| **Total** | | **33 hours (~4-5 days)** |

---

## Next Steps After DataTable

1. **Relationships & Lookups** - Complete lookup field UI
2. **Module Builder** - Enhance with field templates
3. **Workflows** - Visual workflow builder
4. **Reporting** - Custom reports and dashboards
5. **Mobile App** - Native mobile interface

---

## Development Guidelines

### Code Quality
- Follow **Hexagonal Architecture** principles
- Use **Domain-Driven Design** for entities
- Write tests **before** marking complete
- Run `composer pint` before PHP commits
- Run `npm run format` before JS commits

### Performance Targets
- DataTable initial load: **< 500ms**
- Filter application: **< 200ms**
- Pagination: **< 100ms**
- Export 10k records: **< 5s**

### Testing Requirements
- **Unit tests** for services and repositories
- **Feature tests** for API endpoints
- **Browser tests** for user interactions
- Maintain **80%+ code coverage**

---

## Notes

### Current State (2025-11-14)
- ✅ DataTable displays data with pagination
- ✅ Filters work for text, number, date, boolean
- ✅ Column visibility, order, width saved per user
- ✅ Sort by column working
- ⬜ Row selection not implemented
- ⬜ Bulk operations not implemented
- ⬜ Inline editing not implemented
- ⬜ Export not implemented

### Key Decisions
- Using `@tanstack/table-core` for table logic
- Using shadcn-svelte for UI components
- Storing preferences in `user_table_preferences` table
- Multi-database tenancy (each tenant = separate DB)
