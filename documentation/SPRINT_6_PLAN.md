# Sprint 6: Relationships & Lookup Fields Architecture Plan

## Overview

Sprint 6 implements the Relationships & Lookup Fields system for VrtxCRM, enabling tenants to create one-to-many and many-to-many relationships between custom modules. This feature transforms the CRM from isolated module records into an interconnected data model.

**Key Features:**
- Lookup field type for establishing relationships between modules
- Relationship metadata storage in `module_relationships` table
- Multi-step record selection UI with search and filtering
- Related records display in detail views
- Cascade delete protection and orphan handling
- Inverse relationship support for bidirectional navigation

---

## Architecture Overview

### Hexagonal (Ports & Adapters) + DDD Pattern

Following the established architecture:

**Domain Layer** (`app/Domain/Modules/`)
- **Entities**: Relationship (new), Field with relationship support
- **Value Objects**: RelationshipType, RelationshipSettings
- **Repository Interfaces**: ModuleRelationshipRepositoryInterface

**Infrastructure Layer** (`app/Infrastructure/Persistence/Eloquent/`)
- **Models**: ModuleRelationshipModel (new), ModuleRecordModel with relationship methods
- **Repositories**: EloquentModuleRelationshipRepository (new)

**Application Layer** (`app/Http/Controllers/Api/`)
- **Controllers**: ModuleRelationshipController (new), ModuleRecordController enhancements
- **Validation**: Relationship creation/update rules

**Frontend Layer** (`resources/js/`)
- **Components**: LookupField, RelatedRecordsDisplay, RecordSelector
- **Pages**: Relationship configuration UI (integrated into module builder)

### Data Isolation with Multi-Tenancy

- All relationship definitions are tenant-specific (stored in `module_relationships` table)
- Cross-tenant relationships are impossible due to separate databases
- Lookup field values stored as record IDs in `module_records.data` JSON
- Relationship metadata controls display and behavior per tenant

---

## Database Schema Design

### 1. Module Relationships Table (Existing)

**Table: `module_relationships`**

```sql
CREATE TABLE module_relationships (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    from_module_id BIGINT UNSIGNED NOT NULL (FK -> modules.id),
    to_module_id BIGINT UNSIGNED NOT NULL (FK -> modules.id),
    type VARCHAR(50) NOT NULL, -- 'one_to_many', 'many_to_many'
    name VARCHAR(255) NOT NULL, -- Relationship name (e.g., "contacts", "deals")
    inverse_name VARCHAR(255) NULLABLE, -- Inverse relationship (e.g., "account")
    settings JSON NULLABLE, -- {cascade_delete: bool, required: bool, ...}
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_from_module (from_module_id),
    INDEX idx_to_module (to_module_id),
    UNIQUE uk_from_module_name (from_module_id, name),
    FOREIGN KEY (from_module_id) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (to_module_id) REFERENCES modules(id) ON DELETE CASCADE
);
```

**Settings JSON Structure:**
```json
{
  "cascade_delete": false,
  "required": false,
  "allow_create_related": true,
  "display_field": "name",
  "sort_field": "created_at",
  "sort_direction": "desc",
  "max_records": null,
  "filter_conditions": null
}
```

### 2. Fields Table Enhancement

**New Column for Lookup Fields:**

```sql
ALTER TABLE fields ADD COLUMN relationship_id BIGINT UNSIGNED NULLABLE AFTER settings;
ALTER TABLE fields ADD FOREIGN KEY (relationship_id) REFERENCES module_relationships(id) ON DELETE CASCADE;
ALTER TABLE fields ADD INDEX idx_relationship (relationship_id);
```

**Purpose**: Lookup field now references the relationship definition it displays through field.settings.relationship_id

### 3. Module Records Table (No Changes Required)

**Existing Structure Supports Relationships:**

```sql
CREATE TABLE module_records (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    module_id BIGINT UNSIGNED NOT NULL,
    data JSON, -- Contains lookup field values as arrays of related record IDs
    created_by BIGINT UNSIGNED NULLABLE,
    updated_by BIGINT UNSIGNED NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,

    KEY idx_module (module_id),
    KEY idx_created_by (created_by),
    KEY idx_created_at (created_at)
);
```

**Data Structure Example:**
```json
{
  "name": "Acme Corp",
  "industry": "Technology",
  "contacts": [15, 23, 45], // Array of related record IDs
  "primary_contact": 23     // Single lookup field
}
```

### 4. New Junction Table for Many-to-Many (Optional, Future)

**For true many-to-many relationships, optional advanced implementation:**

```sql
CREATE TABLE module_relationship_records (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    relationship_id BIGINT UNSIGNED NOT NULL,
    from_record_id BIGINT UNSIGNED NOT NULL,
    to_record_id BIGINT UNSIGNED NOT NULL,
    data JSON NULLABLE, -- For storing junction table data
    created_at TIMESTAMP,

    UNIQUE uk_relationship_records (relationship_id, from_record_id, to_record_id),
    FOREIGN KEY (relationship_id) REFERENCES module_relationships(id) ON DELETE CASCADE,
    KEY idx_from_record (from_record_id),
    KEY idx_to_record (to_record_id)
);
```

**Note**: This is OPTIONAL for Phase 1. Can be implemented in Phase 2 if needed for advanced use cases.

---

## API Endpoints Design

### Module Relationships API

**Base Path**: `/api/relationships`

#### 1. Create Relationship
```
POST /api/relationships
Body: {
  from_module_id: number,
  to_module_id: number,
  type: 'one_to_many' | 'many_to_many',
  name: string,
  inverse_name?: string,
  settings?: {
    cascade_delete: boolean,
    required: boolean,
    allow_create_related: boolean,
    display_field: string,
    sort_field: string,
    sort_direction: 'asc' | 'desc'
  }
}
Response: 201
{
  data: {
    id: number,
    from_module_id: number,
    to_module_id: number,
    type: string,
    name: string,
    inverse_name: string | null,
    settings: object,
    created_at: timestamp
  }
}
```

#### 2. Get Relationship
```
GET /api/relationships/{id}
Response: 200
{
  data: { ...relationship }
}
```

#### 3. Update Relationship
```
PUT /api/relationships/{id}
Body: { name?, inverse_name?, settings? }
Response: 200
{
  data: { ...updated relationship }
}
```

#### 4. Delete Relationship
```
DELETE /api/relationships/{id}
Response: 204
```

#### 5. Get Related Records (for lookup field)
```
GET /api/relationships/{relationshipId}/records?search=term&page=1&limit=20
Response: 200
{
  data: [
    { id: 1, name: 'Record Name', ...display_fields },
    { id: 2, name: 'Record Name', ...display_fields }
  ],
  meta: {
    total: 45,
    per_page: 20,
    current_page: 1,
    last_page: 3
  }
}
```

### Module Records API Enhancements

#### 1. Get Record with Related Records
```
GET /api/modules/{moduleApiName}/records/{id}
Response: 200
{
  data: {
    id: number,
    module_id: number,
    data: {
      ...fields,
      contacts: [
        { id: 15, name: 'John Doe', email: 'john@example.com' },
        { id: 23, name: 'Jane Smith', email: 'jane@example.com' }
      ]
    },
    related_counts: {
      contacts: 2,
      deals: 5
    }
  }
}
```

#### 2. Create/Update with Lookup Fields
```
POST/PUT /api/modules/{moduleApiName}/records
Body: {
  name: "Acme Corp",
  industry: "Technology",
  contacts: [15, 23, 45], // Array of record IDs or objects for creation
  primary_contact: 23
}
```

#### 3. Add Related Record
```
POST /api/modules/{moduleApiName}/records/{id}/relationships/{relationshipName}
Body: { related_record_id: number }
Response: 201
{
  data: { ...updated record },
  message: "Related record added"
}
```

#### 4. Remove Related Record
```
DELETE /api/modules/{moduleApiName}/records/{id}/relationships/{relationshipName}/{relatedRecordId}
Response: 204
{
  message: "Related record removed"
}
```

---

## Frontend Components Design

### 1. LookupField Component

**Purpose**: Display and manage lookup field in forms

**File**: `resources/js/components/form/LookupField.svelte`

```svelte
<script>
  interface Props {
    label: string;
    name: string;
    relationshipId: number;
    fromModuleId: number;
    toModuleId: number;
    type: 'one_to_many' | 'many_to_many';
    value: number | number[];
    description?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    width?: 25 | 50 | 75 | 100;
    onchange?: (value: number | number[]) => void;
  }

  let {
    label,
    name,
    relationshipId,
    type,
    value = $bindable([]),
    description,
    error,
    required = false,
    disabled = false,
    width = 100,
    onchange
  }: Props = $props();

  let isOpen = $state(false);
  let selectedRecords = $state<Record[]>([]);
  let searchQuery = $state('');
  let searchResults = $state<Record[]>([]);
  let isSearching = $state(false);
  let page = $state(1);
  let totalPages = $state(1);

  // Load selected records initially
  onMount(async () => {
    if (Array.isArray(value) && value.length > 0) {
      await loadSelectedRecords(value);
    }
  });

  async function handleSearch(query: string) {
    if (!query || query.length < 2) {
      searchResults = [];
      return;
    }

    isSearching = true;
    const response = await fetch(
      `/api/relationships/${relationshipId}/records?search=${encodeURIComponent(query)}&page=${page}`
    );
    const json = await response.json();
    searchResults = json.data;
    totalPages = json.meta.last_page;
    isSearching = false;
  }

  function toggleSelection(record: Record) {
    if (type === 'one_to_many') {
      // Multi-select
      if (isSelected(record.id)) {
        value = value.filter((id: number) => id !== record.id);
        selectedRecords = selectedRecords.filter(r => r.id !== record.id);
      } else {
        (value as number[]).push(record.id);
        selectedRecords.push(record);
      }
    } else {
      // Single select
      value = record.id;
      selectedRecords = [record];
      isOpen = false;
    }
    onchange?.(value);
  }

  function removeSelected(recordId: number) {
    if (Array.isArray(value)) {
      value = value.filter((id: number) => id !== recordId);
    }
    selectedRecords = selectedRecords.filter(r => r.id !== recordId);
    onchange?.(value);
  }

  function isSelected(recordId: number): boolean {
    return Array.isArray(value)
      ? value.includes(recordId)
      : value === recordId;
  }
</script>

<FieldBase {label} {name} {description} {error} {required} {disabled} {width}>
  {#snippet children(props)}
    <div class="space-y-2">
      {/* Selected Records Display */}
      <div class="flex flex-wrap gap-2">
        {#each selectedRecords as record (record.id)}
          <div class="flex items-center gap-2 bg-secondary px-2 py-1 rounded">
            <span>{record.name}</span>
            <button
              onclick={() => removeSelected(record.id)}
              disabled={disabled}
              class="ml-1 text-xs text-muted-foreground hover:text-destructive"
            >
              ×
            </button>
          </div>
        {/each}
      </div>

      {/* Search and Selection */}
      <Popover bind:open={isOpen} {disabled}>
        <PopoverTrigger asChild let:builder>
          <Button
            builders={[builder]}
            variant="outline"
            role="combobox"
            class="w-full justify-between"
          >
            {selectedRecords.length > 0
              ? `${selectedRecords.length} selected`
              : 'Select records...'}
            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent class="w-full p-0">
          <Command>
            <CommandInput
              placeholder="Search records..."
              value={searchQuery}
              onValueChange={(value) => {
                searchQuery = value;
                handleSearch(value);
              }}
            />
            <CommandEmpty>No records found.</CommandEmpty>
            <CommandGroup>
              {#each searchResults as record (record.id)}
                <CommandItem
                  value={record.id}
                  onSelect={() => toggleSelection(record)}
                >
                  <Check
                    class="mr-2 h-4 w-4"
                    classList={{
                      'opacity-0': !isSelected(record.id),
                    }}
                  />
                  {record.name}
                </CommandItem>
              {/each}
            </CommandGroup>
          </Command>
        </PopoverContent>
      </Popover>
    </div>
  {/snippet}
</FieldBase>
```

**Features**:
- Search-enabled combobox for finding related records
- Multi-select for one_to_many relationships
- Single-select for many_to_many relationships (Phase 1)
- Display of selected records with remove capability
- Pagination support
- Debounced search input

### 2. RelatedRecordsDisplay Component

**Purpose**: Display related records in detail view

**File**: `resources/js/components/modules/RelatedRecordsDisplay.svelte`

```svelte
<script lang="ts">
  import { DataTable } from '@/components/ui/data-table';
  import { Button } from '@/components/ui/button';

  interface Props {
    relationshipId: number;
    relationshipName: string;
    relationshipType: string;
    relatedRecords: Record[];
    relatedModule: Module;
    recordId: number;
    moduleApiName: string;
    onAddRecord?: (recordId: number) => void;
    onRemoveRecord?: (recordId: number) => void;
  }

  let {
    relationshipId,
    relationshipName,
    relationshipType,
    relatedRecords,
    relatedModule,
    recordId,
    moduleApiName,
    onAddRecord,
    onRemoveRecord
  }: Props = $props();

  let isAdding = $state(false);

  async function handleAddRecord() {
    isAdding = true;
    try {
      const response = await fetch(
        `/api/modules/${moduleApiName}/records/${recordId}/relationships/${relationshipName}`,
        {
          method: 'POST',
          body: JSON.stringify({ related_record_id: selectedRecordId }),
          headers: { 'Content-Type': 'application/json' }
        }
      );

      if (response.ok) {
        const data = await response.json();
        onAddRecord?.(selectedRecordId);
      }
    } finally {
      isAdding = false;
    }
  }

  async function handleRemoveRecord(relatedRecordId: number) {
    const confirmed = confirm('Remove this related record?');
    if (!confirmed) return;

    try {
      const response = await fetch(
        `/api/modules/${moduleApiName}/records/${recordId}/relationships/${relationshipName}/${relatedRecordId}`,
        { method: 'DELETE' }
      );

      if (response.ok) {
        onRemoveRecord?.(relatedRecordId);
      }
    } catch (error) {
      console.error('Error removing related record:', error);
    }
  }
</script>

<Card>
  <CardHeader>
    <CardTitle>{relationshipName}</CardTitle>
    <CardDescription>
      {relatedRecords.length} {relationshipName.toLowerCase()}
    </CardDescription>
  </CardHeader>
  <CardContent>
    {#if relatedRecords.length === 0}
      <p class="text-sm text-muted-foreground">No related records</p>
    {:else}
      <DataTable
        data={relatedRecords}
        columns={getDisplayColumns(relatedModule)}
      >
        <svelte:fragment slot="actions" let:row>
          <Button
            variant="ghost"
            size="sm"
            onclick={() => handleRemoveRecord(row.original.id)}
          >
            Remove
          </Button>
        </svelte:fragment>
      </DataTable>
    {/if}

    {#if relationshipType === 'one_to_many'}
      <Button
        onclick={handleAddRecord}
        disabled={isAdding}
        class="mt-4 w-full"
      >
        Add {relatedModule.singular_name}
      </Button>
    {/if}
  </CardContent>
</Card>
```

**Features**:
- Displays related records in a data table
- Shows record count
- Add/remove related records
- Responsive layout
- Empty state handling

### 3. RecordSelector Component

**Purpose**: Modal for selecting and adding related records

**File**: `resources/js/components/modules/RecordSelector.svelte`

```svelte
<script lang="ts">
  interface Props {
    relationshipId: number;
    relationshipType: string;
    onSelect?: (recordIds: number | number[]) => void;
    onCancel?: () => void;
  }

  let { relationshipId, relationshipType, onSelect, onCancel }: Props = $props();

  let searchQuery = $state('');
  let selectedRecords = $state<number[]>([]);
  let records = $state<Record[]>([]);
  let page = $state(1);
  let totalPages = $state(1);
  let isLoading = $state(false);

  async function searchRecords(query: string) {
    isLoading = true;
    const response = await fetch(
      `/api/relationships/${relationshipId}/records?search=${query}&page=${page}`
    );
    const json = await response.json();
    records = json.data;
    totalPages = json.meta.last_page;
    isLoading = false;
  }

  function toggleSelection(recordId: number) {
    if (relationshipType === 'many_to_many') {
      // Single select
      selectedRecords = [recordId];
    } else {
      // Multi-select
      if (selectedRecords.includes(recordId)) {
        selectedRecords = selectedRecords.filter(id => id !== recordId);
      } else {
        selectedRecords.push(recordId);
      }
    }
  }

  function handleSelect() {
    if (relationshipType === 'many_to_many' && selectedRecords.length === 1) {
      onSelect?.(selectedRecords[0]);
    } else {
      onSelect?.(selectedRecords);
    }
  }
</script>

<Dialog open>
  <DialogContent class="max-w-2xl">
    <DialogHeader>
      <DialogTitle>Select Records</DialogTitle>
    </DialogHeader>

    <div class="space-y-4">
      <Input
        placeholder="Search records..."
        value={searchQuery}
        onchange={(e) => {
          searchQuery = e.target.value;
          searchRecords(searchQuery);
        }}
      />

      <div class="max-h-96 overflow-y-auto space-y-2">
        {#each records as record (record.id)}
          <div class="flex items-center gap-2 p-2 hover:bg-secondary rounded">
            <input
              type={relationshipType === 'many_to_many' ? 'radio' : 'checkbox'}
              checked={selectedRecords.includes(record.id)}
              onchange={() => toggleSelection(record.id)}
            />
            <span>{record.name}</span>
          </div>
        {/each}
      </div>

      <div class="flex gap-2">
        <Button variant="outline" onclick={() => onCancel?.()}>
          Cancel
        </Button>
        <Button onclick={handleSelect} disabled={selectedRecords.length === 0}>
          Select
        </Button>
      </div>
    </div>
  </DialogContent>
</Dialog>
```

---

## FieldType & Settings Extensions

### FieldType Enum Update

**File**: `app/Domain/Modules/ValueObjects/FieldType.php`

The `LOOKUP` case already exists. Ensure the `isRelationship()` method is used:

```php
public function isRelationship(): bool
{
    return $this === self::LOOKUP;
}
```

### FieldSettings Value Object Update

**File**: `app/Domain/Modules/ValueObjects/FieldSettings.php`

Already includes `relatedModuleId` property. Add relationship-specific methods:

```php
public function getRelationshipId(): ?int
{
    return $this->relatedModuleId;
}

public function isRelationshipField(): bool
{
    return $this->relatedModuleId !== null;
}

public function withRelationshipSettings(
    int $relatedModuleId,
    array $additionalSettings
): self {
    return new self(
        minLength: $this->minLength,
        maxLength: $this->maxLength,
        minValue: $this->minValue,
        maxValue: $this->maxValue,
        pattern: $this->pattern,
        precision: $this->precision,
        currencyCode: $this->currencyCode,
        relatedModuleId: $relatedModuleId,
        formula: $this->formula,
        allowedFileTypes: $this->allowedFileTypes,
        maxFileSize: $this->maxFileSize,
        additionalSettings: $additionalSettings,
    );
}
```

---

## Implementation Phases

### Phase 1: Foundation & Infrastructure (Weeks 1-2)

**Objectives:**
- Implement domain entities and value objects
- Create database migrations
- Implement repositories
- Set up API endpoints

**Tasks:**

1. **Domain Layer**
   - Create `Relationship` entity in `app/Domain/Modules/Entities/Relationship.php`
   - Create `RelationshipType` value object
   - Create `RelationshipSettings` value object
   - Update `Field` entity to support relationships
   - Create `ModuleRelationshipRepositoryInterface`

2. **Infrastructure Layer**
   - Create `ModuleRelationshipModel` Eloquent model
   - Create `EloquentModuleRelationshipRepository`
   - Create database migration for `fields.relationship_id` column
   - Add indexes and foreign keys

3. **API Implementation**
   - Create `ModuleRelationshipController`
   - Implement CRUD endpoints for relationships
   - Implement "get related records" endpoint
   - Add validation for relationship creation

4. **Testing**
   - Unit tests for domain entities
   - Feature tests for relationship CRUD
   - Integration tests for repository

**Success Criteria:**
- All API endpoints functional and tested
- Relationships can be created, read, updated, deleted
- Data integrity enforced via foreign keys
- Comprehensive test coverage (80%+)

### Phase 2: Lookup Field Backend (Weeks 2-3)

**Objectives:**
- Implement lookup field validation
- Enhance record creation/update with relationships
- Handle cascade deletes and orphan records

**Tasks:**

1. **Record Validation**
   - Update `ModuleRecordController::buildValidationRules()`
   - Validate lookup field values against related module records
   - Support array validation for multi-select

2. **Record Management**
   - Update `store()` method to handle lookup fields
   - Update `update()` method to handle relationship changes
   - Implement related record attachment/detachment

3. **Relationship Handling**
   - Implement cascade delete logic
   - Handle orphan records when deleting related records
   - Implement inverse relationship updates

4. **API Enhancements**
   - Add `GET /api/relationships/{id}/records` endpoint
   - Add `POST/DELETE` endpoints for managing related records
   - Enhance record response to include related records

5. **Testing**
   - Test record creation with relationships
   - Test cascade delete behavior
   - Test orphan record handling
   - Test validation of related record IDs

**Success Criteria:**
- Records can be created with lookup field values
- Lookup field values validated against related module
- Related records properly attached/detached
- Cascade delete logic working correctly
- 80%+ test coverage for new code

### Phase 3: Frontend Components (Weeks 3-4)

**Objectives:**
- Implement LookupField component
- Integrate with form system
- Implement record selection UI

**Tasks:**

1. **LookupField Component**
   - Create `LookupField.svelte`
   - Implement search functionality
   - Implement multi-select/single-select
   - Add loading states
   - Implement pagination support

2. **Form Integration**
   - Add LookupField to dynamic form renderer
   - Update form component exports
   - Handle field type detection for lookup fields
   - Bind form data to lookup field values

3. **RelatedRecordsDisplay Component**
   - Create `RelatedRecordsDisplay.svelte`
   - Display related records in data table
   - Implement add/remove related record buttons
   - Add loading and error states

4. **RecordSelector Component**
   - Create modal for selecting related records
   - Implement search and filtering
   - Handle multi-select vs single-select

5. **Testing**
   - Component unit tests with Vitest
   - Integration tests with mock API
   - Accessibility testing (ARIA attributes)
   - User interaction testing

**Success Criteria:**
- LookupField renders correctly
- Search functionality works with debouncing
- Records can be selected/deselected
- Related records display properly
- Components are fully accessible
- 80%+ test coverage

### Phase 4: Module Builder Integration (Weeks 4-5)

**Objectives:**
- Add relationship configuration UI
- Integrate lookup field creation in module builder
- Test end-to-end workflow

**Tasks:**

1. **Relationship Configuration UI**
   - Create relationship wizard in module builder
   - UI for selecting "from" and "to" modules
   - UI for configuring relationship settings
   - Inverse relationship name input

2. **Lookup Field Creation**
   - Add lookup field to field creation form
   - UI to select which relationship to use
   - Configuration for display and sort fields
   - Settings for cascade delete, required, etc.

3. **Module Builder Updates**
   - Integrate relationship configuration page
   - Update module overview to show relationships
   - Add relationship deletion with confirmation
   - Add inverse relationship display

4. **End-to-End Testing**
   - Create relationship and lookup field
   - Create records with relationships
   - View related records in detail page
   - Test cascade delete behavior

5. **Documentation**
   - Update CLAUDE.md with relationship system docs
   - Create example usage guide
   - Document API endpoints

**Success Criteria:**
- Relationships can be created in UI
- Lookup fields can be configured in module builder
- Module records can be created with relationships
- Related records display in detail views
- Full workflow tested and working
- Documentation complete

### Phase 5: Advanced Features & Optimization (Weeks 5-6)

**Objectives:**
- Implement many-to-many with junction table (optional)
- Add relationship filtering and search
- Performance optimization

**Tasks:**

1. **Many-to-Many Support (Optional)**
   - Create `module_relationship_records` junction table
   - Update relationship type logic
   - Implement junction table data storage

2. **Advanced Filtering**
   - Add filter UI for related records
   - Implement server-side filtering
   - Add relationship-based record filtering

3. **Performance**
   - Add database indexes for relationship queries
   - Implement eager loading for related records
   - Cache relationship definitions
   - Optimize JSON queries

4. **Edge Cases**
   - Handle circular relationships
   - Prevent self-relationships
   - Handle relationship cycles
   - Test with large datasets

5. **Polish**
   - UI/UX improvements
   - Error messaging refinement
   - Loading state optimizations
   - Accessibility audit

**Success Criteria:**
- All edge cases handled gracefully
- Query performance optimized
- No N+1 queries in related record loading
- Comprehensive error handling
- Production-ready code

---

## Testing Strategy

### Unit Tests

**Domain Layer** (`tests/Unit/Modules/Relationships/`)
```
- RelationshipEntityTest.php
- RelationshipTypeTest.php
- RelationshipSettingsTest.php
- FieldWithRelationshipTest.php
```

**Test Coverage:**
- Entity creation and validation
- Value object serialization/deserialization
- Settings JSON handling
- Relationship type rules

### Feature Tests

**Repository Tests** (`tests/Feature/Modules/Relationships/`)
```
- ModuleRelationshipRepositoryTest.php
  - Create relationship
  - Update relationship
  - Delete relationship
  - Find relationships

- ModuleRecordRepositoryTest.php
  - Create record with relationships
  - Update record relationships
  - Load related records
  - Handle cascade deletes
```

**API Tests** (`tests/Feature/Api/`)
```
- ModuleRelationshipControllerTest.php
  - POST /api/relationships
  - GET /api/relationships/{id}
  - PUT /api/relationships/{id}
  - DELETE /api/relationships/{id}
  - GET /api/relationships/{id}/records

- ModuleRecordControllerTest.php (updated)
  - Create record with lookup fields
  - Update record relationships
  - GET record with related records
  - POST/DELETE related record endpoints
```

**Validation Tests:**
- Invalid related record IDs rejected
- Type-specific validation (one_to_many vs many_to_many)
- Cascade delete behavior
- Orphan record handling

### Frontend Tests

**Component Tests** (`tests/components/`)
```
- LookupField.spec.ts
  - Renders search input
  - Handles selection
  - Displays selected records
  - Pagination works

- RelatedRecordsDisplay.spec.ts
  - Displays related records
  - Add/remove buttons work
  - Empty state renders

- RecordSelector.spec.ts
  - Opens modal
  - Search filters records
  - Selection works
```

### Integration Tests

**End-to-End** (`tests/e2e/`)
```
- Create relationship
- Create lookup field
- Create record with relationships
- View record detail with related records
- Add/remove related records
- Cascade delete test
```

### Test Data & Fixtures

**Seed Data** (`database/seeders/RelationshipSeeder.php`)
- Sample relationships:
  - Accounts hasMany Contacts
  - Contacts belongsTo Account
  - Deals belongsToMany Products
  - Products belongsToMany Deals

**Test Database Setup:**
- Pre-populated test modules
- Test relationship definitions
- Sample records for testing

---

## Success Criteria

### Functional Criteria

1. **Relationship Management**
   - Create one-to-many relationships between modules
   - Create many-to-many relationships (with junction table in Phase 5)
   - Update relationship definitions
   - Delete relationships with validation
   - Inverse relationship support functional

2. **Lookup Fields**
   - Lookup field type available in field creation
   - Search and select related records
   - Multi-select for one_to_many relationships
   - Display selected records
   - Remove related records from form

3. **Record Management**
   - Create records with lookup field values
   - Update lookup field values
   - View related records in detail view
   - Add/remove related records from detail view
   - Cascade delete working correctly

4. **Data Integrity**
   - Cannot create relationships to non-existent modules
   - Cannot create lookup fields without relationship
   - Related record IDs validated on record creation
   - Foreign key constraints enforced
   - Orphan records handled gracefully

### Quality Criteria

1. **Testing**
   - 80%+ code coverage for backend
   - All critical paths tested
   - Integration tests passing
   - E2E tests covering workflows

2. **Performance**
   - Related records load in < 200ms
   - Relationship queries use indexes
   - No N+1 query problems
   - Large dataset handling (10,000+ records)

3. **Code Quality**
   - Follows hexagonal architecture
   - Clear separation of concerns
   - Comprehensive documentation
   - Type safety throughout

4. **User Experience**
   - Search-enabled record selection
   - Clear visual feedback
   - Error messages helpful
   - Loading states visible
   - Accessibility compliant (WCAG 2.1 AA)

---

## Risk Mitigation

### Technical Risks

| Risk | Impact | Mitigation |
|------|--------|-----------|
| N+1 query problems | Performance | Use eager loading, implement caching |
| Circular relationships | Data integrity | Validate relationships on creation |
| Large related record sets | Performance | Implement pagination, lazy loading |
| Cascade delete bugs | Data loss | Comprehensive testing, validation |

### Timeline Risks

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Scope creep (many-to-many) | Delays | Define Phase 1 scope clearly, defer Phase 5 |
| Frontend complexity | Delays | Component-based approach, early testing |
| Integration challenges | Delays | Weekly integration tests, early E2E tests |

### Data Risks

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Migration issues | Production | Test migrations thoroughly, backup strategy |
| Data inconsistency | Corruption | Foreign key constraints, transaction handling |
| Tenant data isolation | Security | Verify per-tenant filtering, audit logs |

---

## Deliverables

### Code

1. **Backend**
   - Domain entities and value objects
   - Repository implementations
   - API controllers
   - Migrations
   - Tests (unit, feature, integration)

2. **Frontend**
   - Lookup field component
   - Related records display component
   - Record selector modal
   - Integration with form system
   - Tests and fixtures

3. **Documentation**
   - Updated CLAUDE.md
   - API endpoint documentation
   - Component prop documentation
   - Example usage guide
   - Architecture diagrams

### Metrics

- **Code Coverage**: 80%+
- **Performance**: < 200ms for related record queries
- **Test Count**: 100+ tests (unit, feature, integration)
- **Component Accessibility**: 100% WCAG 2.1 AA compliant

---

## Dependencies & Prerequisites

### External Dependencies
- Laravel 12 (already installed)
- Svelte 5 (already installed)
- shadcn-svelte components (already installed)

### Existing Features Required
- Module system (modules, blocks, fields)
- Module record storage (JSON in database)
- Form component system
- API controller patterns
- Repository pattern implementation

### Team Skills Required
- Backend: PHP/Laravel, DDD, repositories pattern
- Frontend: Svelte 5, TypeScript, component design
- Database: SQL, migrations, indexes
- Testing: PHPUnit, Vitest, E2E testing

---

## Timeline Summary

| Phase | Duration | Focus |
|-------|----------|-------|
| Phase 1 | Weeks 1-2 | Domain layer, repos, API |
| Phase 2 | Weeks 2-3 | Validation, cascade delete, API enhancements |
| Phase 3 | Weeks 3-4 | Frontend components |
| Phase 4 | Weeks 4-5 | Module builder integration, E2E |
| Phase 5 | Weeks 5-6 | Optional advanced features, optimization |
| **Total** | **6 weeks** | Production-ready system |

---

## Future Enhancements (Post-Sprint 6)

1. **Advanced Relationship Types**
   - Polymorphic relationships
   - Self-referential relationships (organizational hierarchy)
   - Through relationships (A → B → C)

2. **Relationship Features**
   - Relationship-based filtering and searching
   - Relationship-based workflows
   - Relationship analytics and reporting
   - Bulk relationship management

3. **Performance**
   - Relationship caching layer
   - Materialized views for common queries
   - Denormalization for frequently accessed data

4. **UI/UX**
   - Relationship visualization (graph/network view)
   - Bulk add/remove related records
   - Relationship templates and presets
   - Relationship-based dashboards

---

## References

### Internal
- `/home/chris/PersonalProjects/VrtxCRM/app/Domain/Modules/`
- `/home/chris/PersonalProjects/VrtxCRM/database/migrations/tenant/`
- `/home/chris/PersonalProjects/VrtxCRM/resources/js/components/form/`

### External
- [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Domain-Driven Design](https://en.wikipedia.org/wiki/Domain-driven_design)
- [Hexagonal Architecture](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software))

---

**Document Version**: 1.0
**Last Updated**: 2025-11-12
**Status**: Ready for Implementation

