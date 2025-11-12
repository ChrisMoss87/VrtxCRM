# Module Builder - Implementation Plan

## Overview
The Module Builder is a visual interface that allows users to create and manage custom modules (entities) with fields, blocks, and relationships. This is the core feature of the dynamic CRM system.

## User Journey

### 1. Module List View (`/modules`)
**Purpose**: Overview of all modules in the system

**Components Needed**:
- Module cards/table showing:
  - Icon + Name
  - Description
  - Number of fields
  - Number of records
  - Active/Inactive status
  - Last modified date
- Actions:
  - Create New Module button
  - Edit module
  - Deactivate/Activate module
  - Delete module (with confirmation)
  - View module records

**UI Design**:
- Grid layout for module cards (similar to app icons)
- Each card shows module icon, name, description, field count
- Quick actions on hover/click
- Filter: Active/Inactive/All
- Search modules by name

### 2. Module Builder View (`/modules/create` or `/modules/{id}/edit`)
**Purpose**: Create or edit a module with its structure

**Tab Structure**:
```
┌─────────────────────────────────────────────────────────────┐
│ [Module Details] [Fields & Blocks] [Relationships] [Settings]│
└─────────────────────────────────────────────────────────────┘
```

#### Tab 1: Module Details
**Fields**:
- Module Name (text, required) - e.g., "Contacts"
- Singular Name (text, required) - e.g., "Contact"
- Icon (icon picker with Lucide icons)
- Description (textarea)
- Order (number) - for display ordering

**Preview**:
- Show how the module will appear in navigation

#### Tab 2: Fields & Blocks
**Layout**: Two-panel design

**Left Panel - Structure Tree**:
```
Module: Contacts
├─ Block: Basic Information (section)
│  ├─ First Name (text)
│  ├─ Last Name (text)
│  ├─ Email (email)
│  └─ Phone (phone)
├─ Block: Address (accordion, collapsed)
│  ├─ Street (textarea)
│  ├─ City (text)
│  └─ Country (select)
└─ + Add Block
```

- Drag-and-drop to reorder blocks and fields
- Expand/collapse blocks
- Click to edit
- Delete with confirmation

**Right Panel - Field/Block Editor**:

When Block is selected:
- Block Name (text, required)
- Block Type (select: section, tab, accordion, card)
- Layout Columns (select: 1, 2, 3, 4)
- Collapsible (toggle)
- Collapsed by Default (toggle, only if collapsible)

When Field is selected:
- **Basic Settings**:
  - Field Label (text, required)
  - API Name (text, auto-generated from label, editable)
  - Field Type (select with icons):
    - Text
    - Textarea
    - Number/Decimal
    - Email
    - Phone
    - URL
    - Select/Multiselect
    - Radio
    - Checkbox
    - Toggle
    - Date/DateTime/Time
    - Currency
    - Percent
    - Lookup (relationship)
    - Formula
    - File/Image
    - Rich Text
  - Description (textarea)
  - Help Text (text)

- **Validation Rules**:
  - Required (toggle)
  - Unique (toggle)
  - Min Length / Max Length (for text)
  - Min Value / Max Value (for numbers)
  - Pattern (regex, advanced)
  - Custom Validation Rules (array)

- **Display Settings**:
  - Show in List View (toggle)
  - Show in Detail View (toggle)
  - Searchable (toggle)
  - Width (select: 25%, 50%, 75%, 100%)
  - Default Value (text/number/etc.)

- **Field Type Specific Settings**:
  - For Select/Multiselect/Radio:
    - Options Manager:
      ```
      ┌────────────────────────────────────┐
      │ Label       Value      Color  Default│
      ├────────────────────────────────────┤
      │ Active      active     green   [x]  │
      │ Inactive    inactive   gray    [ ]  │
      │ + Add Option                        │
      └────────────────────────────────────┘
      ```
  - For Currency:
    - Currency Code (select: USD, EUR, GBP, etc.)
    - Precision (decimal places)
  - For Decimal/Percent:
    - Precision (decimal places)
  - For Lookup:
    - Related Module (select from modules)
    - Display Field (select field from related module)
  - For Formula:
    - Formula Expression (textarea with syntax help)
  - For File/Image:
    - Allowed File Types (multiselect)
    - Max File Size (number in KB)

#### Tab 3: Relationships
**Purpose**: Define relationships between modules

**Layout**:
Table showing existing relationships:
| Relationship Name | Related Module | Type | Inverse Name |
|------------------|----------------|------|--------------|
| Account | Accounts | One to Many | Contacts |

**Add Relationship Form**:
- Relationship Name (text, required)
- Related Module (select from modules)
- Relationship Type (select):
  - One to One
  - One to Many
  - Many to Many
- Inverse Relationship Name (text)

#### Tab 4: Settings
**Module Features**:
- Enable Import (toggle)
- Enable Export (toggle)
- Enable Mass Actions (toggle)
- Enable Comments (toggle)
- Enable Attachments (toggle)
- Enable Activity Log (toggle)
- Enable Custom Views (toggle)
- Record Name Field (select from text fields) - used as display name

**Permissions** (future):
- View permission
- Create permission
- Edit permission
- Delete permission

### 3. Field Type Components (Reusable)
Create Svelte components for each field type configuration:
- `FieldTypeSelector.svelte` - Grid of field types with icons
- `TextFieldConfig.svelte` - Text field settings
- `SelectFieldConfig.svelte` - Select with options manager
- `NumberFieldConfig.svelte` - Number/decimal/currency/percent
- `DateFieldConfig.svelte` - Date/time settings
- `LookupFieldConfig.svelte` - Relationship configuration
- `ValidationRulesConfig.svelte` - Validation rules editor
- `DisplaySettingsConfig.svelte` - Display settings

### 4. Module Record Views (After Builder is Complete)
Once modules are created, users need to:
- List records (`/modules/{module_slug}/records`)
- Create record (`/modules/{module_slug}/records/create`)
- View record (`/modules/{module_slug}/records/{id}`)
- Edit record (`/modules/{module_slug}/records/{id}/edit`)

These views must be **dynamically generated** based on module definition.

## Components Architecture

```
pages/modules/
├── Index.svelte              # Module list
├── Create.svelte             # Create module
└── [id]/
    └── Edit.svelte           # Edit module

components/modules/
├── ModuleCard.svelte         # Module grid card
├── ModuleForm.svelte         # Main module form with tabs
├── blocks/
│   ├── BlockList.svelte      # Left panel tree view
│   ├── BlockEditor.svelte    # Block settings editor
│   └── BlockItem.svelte      # Draggable block item
├── fields/
│   ├── FieldEditor.svelte    # Main field editor
│   ├── FieldTypeSelector.svelte
│   ├── FieldBasicSettings.svelte
│   ├── FieldValidation.svelte
│   ├── FieldDisplay.svelte
│   └── field-configs/
│       ├── TextFieldConfig.svelte
│       ├── SelectFieldConfig.svelte
│       ├── NumberFieldConfig.svelte
│       ├── DateFieldConfig.svelte
│       ├── LookupFieldConfig.svelte
│       └── ...
├── relationships/
│   ├── RelationshipList.svelte
│   └── RelationshipForm.svelte
└── settings/
    └── ModuleSettings.svelte
```

## State Management

Use Svelte 5 runes for local component state:
```typescript
let module = $state({
  id: null,
  name: '',
  singular_name: '',
  icon: null,
  description: '',
  is_active: true,
  settings: {},
  order: 0,
  blocks: [],
  fields: [],
  relationships: []
});

let selectedItem = $state(null); // Currently selected block or field
let activeTab = $state('details'); // Current tab
```

## API Endpoints Needed

```
GET    /api/modules                    # List all modules
POST   /api/modules                    # Create module
GET    /api/modules/{id}               # Get module with full structure
PUT    /api/modules/{id}               # Update module
DELETE /api/modules/{id}               # Delete module
PATCH  /api/modules/{id}/activate      # Activate module
PATCH  /api/modules/{id}/deactivate    # Deactivate module

POST   /api/modules/{id}/blocks        # Add block to module
PUT    /api/modules/{id}/blocks/{blockId}        # Update block
DELETE /api/modules/{id}/blocks/{blockId}        # Delete block
POST   /api/modules/{id}/blocks/reorder          # Reorder blocks

POST   /api/modules/{id}/fields        # Add field to module
PUT    /api/modules/{id}/fields/{fieldId}        # Update field
DELETE /api/modules/{id}/fields/{fieldId}        # Delete field
POST   /api/modules/{id}/fields/reorder          # Reorder fields

POST   /api/modules/{id}/relationships  # Add relationship
PUT    /api/modules/{id}/relationships/{relId}   # Update relationship
DELETE /api/modules/{id}/relationships/{relId}   # Delete relationship
```

## Drag and Drop Implementation

Use `svelte-dnd-action` or native HTML5 drag and drop:
- Drag blocks to reorder
- Drag fields between blocks
- Drag fields to reorder within block
- Visual feedback during drag

## Validation

Client-side validation:
- Module name required
- API name must be snake_case
- API name must be unique within module
- Block names required
- Field names required
- Field type required
- Options required for select/multiselect/radio fields

Server-side validation:
- Same as client-side
- Check for SQL reserved keywords in API names
- Validate relationships point to existing modules

## Progressive Enhancement

### Phase 1 (MVP):
- Module details
- Blocks (sections only)
- Basic field types (text, textarea, select, checkbox, toggle)
- No relationships
- Basic settings

### Phase 2:
- All field types
- Field validation rules
- Drag and drop
- Block types (tabs, accordion, cards)

### Phase 3:
- Relationships
- Lookup fields
- Formula fields
- Advanced validation

### Phase 4:
- Import/Export
- Custom views
- Permissions
- Activity log

## Next Steps

1. ✅ Create domain entities and database structure
2. ✅ Create form wrapper components
3. ✅ Seed sample modules
4. Create Application layer (Services, Commands, Queries)
5. Create Controllers and API endpoints
6. Create Module List page
7. Create Module Builder page (Phase 1)
8. Test creating a module through UI
9. Iterate and add Phase 2+ features