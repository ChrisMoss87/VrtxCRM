# Sprint 5: Dynamic Module Frontend

**Sprint Duration**: Sprint 5 (Dynamic Module System - Frontend)
**Status**: 🚀 **STARTING**
**Start Date**: 2025-11-12

---

## Sprint Goal

Build the frontend interface for the dynamic module system, allowing users to view, create, and manage records for custom modules (Contacts, Leads, Deals, Companies).

---

## Prerequisites (Completed ✅)

- ✅ Sprint 3-4: Backend dynamic module system complete
- ✅ Multi-tenant authentication working
- ✅ 4 CRM modules seeded (Contacts, Leads, Deals, Companies)
- ✅ Test tenant configured (acme.vrtxcrm.local)
- ✅ Playwright tests passing (15/18)

---

## Sprint Objectives

### 1. Module Navigation & Routing
- [ ] Create module navigation menu in sidebar
- [ ] Dynamic routing for modules (/modules/{module_api_name})
- [ ] Module list view route
- [ ] Module record detail route
- [ ] Module record edit/create route

### 2. Module List View (Index)
- [ ] Fetch module records from backend API
- [ ] Display records in table/grid format
- [ ] Column configuration based on module fields
- [ ] Sorting by columns
- [ ] Filtering/search functionality
- [ ] Pagination
- [ ] "New Record" button
- [ ] Record actions (view, edit, delete)

### 3. Module Record Detail View
- [ ] Fetch single record by ID
- [ ] Display all fields in organized blocks
- [ ] Format field values based on field type
- [ ] Related records section (if applicable)
- [ ] Activity timeline placeholder
- [ ] Edit/Delete buttons
- [ ] Breadcrumb navigation

### 4. Module Record Form (Create/Edit)
- [ ] Dynamic form generation from module definition
- [ ] Render fields by type using existing field wrappers
- [ ] Block/section organization
- [ ] Field validation based on field settings
- [ ] Form submission to backend API
- [ ] Success/error handling
- [ ] Cancel navigation
- [ ] Save and continue editing

### 5. Backend API Endpoints
- [ ] `GET /api/modules` - List all modules for current tenant
- [ ] `GET /api/modules/{module}/records` - List records with pagination
- [ ] `GET /api/modules/{module}/records/{id}` - Get single record
- [ ] `POST /api/modules/{module}/records` - Create record
- [ ] `PUT /api/modules/{module}/records/{id}` - Update record
- [ ] `DELETE /api/modules/{module}/records/{id}` - Delete record
- [ ] `GET /api/modules/{module}` - Get module definition (fields, blocks)

### 6. Svelte Components
- [ ] `ModuleList.svelte` - List view page
- [ ] `ModuleDetail.svelte` - Detail view page
- [ ] `ModuleForm.svelte` - Create/edit form page
- [ ] `ModuleTable.svelte` - Reusable table component
- [ ] `DynamicField.svelte` - Field renderer component
- [ ] `FieldValue.svelte` - Field value display component
- [ ] `ModuleNav.svelte` - Module navigation component

---

## Technical Implementation Plan

### Phase 1: Backend API (Day 1)
1. Create `ModuleController` with REST endpoints
2. Create `ModuleRecordController` with CRUD endpoints
3. Add API routes to `routes/tenant.php`
4. Test endpoints with Postman/Insomnia
5. Add API middleware (auth, tenant context)

### Phase 2: List View (Day 2)
1. Create `ModuleList.svelte` page
2. Fetch module definition and records
3. Build dynamic table with module fields as columns
4. Implement sorting and pagination
5. Add "New Record" and row actions
6. Add to module navigation

### Phase 3: Detail View (Day 3)
1. Create `ModuleDetail.svelte` page
2. Fetch single record with module definition
3. Display fields organized by blocks
4. Format values based on field types
5. Add edit/delete actions
6. Wire up breadcrumb navigation

### Phase 4: Form View (Day 4)
1. Create `ModuleForm.svelte` page
2. Dynamic form generation from module definition
3. Use existing field wrappers (TextField, SelectField, etc.)
4. Implement field validation
5. Handle form submission
6. Success/error states
7. Route back to list or detail

### Phase 5: Integration & Testing (Day 5)
1. Test full CRUD flow for Contacts module
2. Test with other modules (Leads, Deals, Companies)
3. Add Playwright E2E tests
4. Fix bugs and edge cases
5. Document usage

---

## User Stories

### Story 1: View Module List
**As a** user
**I want to** see a list of all my contacts
**So that** I can browse and manage them

**Acceptance Criteria:**
- Navigate to "Contacts" from sidebar
- See table with columns: Name, Email, Phone, Status
- See pagination if > 50 records
- Can click row to view detail

### Story 2: View Record Detail
**As a** user
**I want to** view a single contact's details
**So that** I can see all their information

**Acceptance Criteria:**
- Click contact from list → detail page loads
- See all fields organized by blocks
- See formatted values (emails as links, dates formatted)
- Can click Edit button

### Story 3: Create New Record
**As a** user
**I want to** create a new contact
**So that** I can add them to my CRM

**Acceptance Criteria:**
- Click "New Contact" button → form page loads
- See form with all fields organized by blocks
- Required fields marked with *
- Form validates on submit
- Success → redirect to detail page

### Story 4: Edit Existing Record
**As a** user
**I want to** edit a contact's information
**So that** I can keep it up to date

**Acceptance Criteria:**
- Click Edit on detail page → form pre-filled
- Can change any field
- Form validates on submit
- Success → redirect to detail page

### Story 5: Delete Record
**As a** user
**I want to** delete a contact
**So that** I can remove outdated records

**Acceptance Criteria:**
- Click Delete on detail page
- See confirmation dialog
- Confirm → record deleted
- Redirect to list page

---

## Data Flow

### List View Flow
```
User → Click "Contacts" in sidebar
  ↓
ModuleList.svelte loads
  ↓
Fetch GET /api/modules/contacts
  → Returns module definition
  ↓
Fetch GET /api/modules/contacts/records?page=1
  → Returns paginated records
  ↓
Render ModuleTable with dynamic columns
  ↓
User clicks row → Navigate to detail view
```

### Detail View Flow
```
User → Navigate to /modules/contacts/123
  ↓
ModuleDetail.svelte loads
  ↓
Fetch GET /api/modules/contacts
  → Returns module definition
  ↓
Fetch GET /api/modules/contacts/records/123
  → Returns single record
  ↓
Render fields by blocks
Format values by field type
  ↓
User clicks Edit → Navigate to form view
```

### Form Flow (Create)
```
User → Click "New Contact"
  ↓
ModuleForm.svelte loads
  ↓
Fetch GET /api/modules/contacts
  → Returns module definition
  ↓
Render dynamic form with field wrappers
  ↓
User fills form and clicks Save
  ↓
POST /api/modules/contacts/records
  → Validates and creates record
  ↓
Success → Navigate to detail view
```

---

## File Structure

```
resources/js/
├── pages/
│   └── modules/
│       ├── Index.svelte              # Module list view
│       ├── Show.svelte               # Module detail view
│       ├── Create.svelte             # Module create form
│       └── Edit.svelte               # Module edit form
├── components/
│   └── modules/
│       ├── ModuleTable.svelte        # Dynamic table component
│       ├── ModuleTableRow.svelte     # Table row component
│       ├── DynamicForm.svelte        # Dynamic form builder
│       ├── DynamicField.svelte       # Field renderer
│       ├── FieldValue.svelte         # Field value display
│       └── ModuleNav.svelte          # Module navigation
├── types/
│   └── modules.d.ts                  # TypeScript types
└── lib/
    └── modules.ts                    # Helper functions

app/Http/Controllers/
├── ModuleController.php              # Module metadata
└── ModuleRecordController.php        # Record CRUD

routes/
└── tenant.php                        # Add module routes
```

---

## Key Technical Decisions

### 1. Field Rendering Strategy
**Decision**: Use existing field wrapper components (TextField, SelectField, etc.)
**Rationale**: Already built and tested, consistent UI, type-safe

### 2. API Design
**Decision**: RESTful API with nested resources
**Rationale**: Standard, predictable, easy to understand

### 3. Field Type Mapping
Map database field types to Svelte components:
- `text`, `email`, `phone`, `url` → `TextField`
- `textarea`, `rich_text` → `TextareaField`
- `select`, `radio` → `SelectField`
- `multiselect` → `SelectField` (multiple)
- `checkbox`, `toggle` → `CheckboxField` / `SwitchField`
- `date`, `datetime`, `time` → `DateField` (to be created)
- `number`, `decimal`, `currency`, `percent` → `TextField` (type=number)

### 4. Validation Strategy
**Decision**: Client-side validation using field settings, server-side using Laravel validation
**Rationale**: Best UX with client validation, security with server validation

---

## Success Criteria

- [ ] Can navigate to Contacts module
- [ ] Can see list of contacts with pagination
- [ ] Can view single contact detail
- [ ] Can create new contact with all fields
- [ ] Can edit existing contact
- [ ] Can delete contact
- [ ] All CRUD operations work for Leads, Deals, Companies
- [ ] Form validation working
- [ ] Responsive design (mobile-friendly)
- [ ] Playwright tests covering main flows

---

## Out of Scope (Future Sprints)

- Bulk operations (import, export, bulk edit)
- Advanced filtering (date ranges, multi-select)
- Custom views and saved filters
- Kanban view
- Module builder UI (create modules via UI)
- Field dependencies and conditional logic
- File upload handling
- Relationship field rendering

---

## Dependencies

- Svelte 5 with runes ✅
- Inertia.js 2 ✅
- shadcn-svelte components ✅
- Existing field wrappers ✅
- Backend module system ✅
- Multi-tenant authentication ✅

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Complex dynamic form rendering | Medium | High | Use existing field components, start simple |
| API performance with many fields | Low | Medium | Implement pagination, optimize queries |
| Field validation complexity | Medium | Medium | Use Laravel validation, keep rules simple |
| Type safety with dynamic data | Medium | Low | Define TypeScript types, use strict mode |

---

## Notes

- Start with Contacts module as primary example
- Ensure mobile responsiveness from the start
- Keep UI simple and clean (Salesforce/HubSpot-style)
- Use existing UI components from shadcn-svelte
- Document as we go

---

**Ready to Start Sprint 5!** 🚀

First task: Create backend API endpoints for modules and records.
