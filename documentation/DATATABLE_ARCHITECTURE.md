all# Advanced DataTable Architecture

**Created**: 2025-11-12
**Status**: Planning Phase
**Goal**: Build a comprehensive, reusable data table system for VrtxCRM

---

## Overview

A production-ready, feature-rich data table component that can be used across the entire application for displaying and managing records from any module. Built with Svelte 5, TanStack Table, and designed for maximum reusability.

---

## Core Features

### 1. Sorting
- **Single column sort**: Click column header to sort asc/desc/none
- **Multi-column sort**: Hold Shift + click for secondary sorting
- **Backend sorting**: API handles sorting for large datasets
- **Sort indicators**: Visual arrows showing sort direction and priority

### 2. Filtering
- **Column-specific filters**:
  - Text fields: Contains, equals, starts with, ends with
  - Numbers: Equals, greater than, less than, between
  - Dates: Single date, date range (from/to), relative (last 7 days, this month, etc.)
  - Select fields: Multi-select dropdown with checkboxes
  - Boolean: Yes/No/All toggle
  - Lookup fields: Search and select related records
- **Global search**: Search across all searchable columns
- **Filter persistence**: Save filters with views
- **Clear all filters**: Quick reset button

### 3. Pagination
- **Page size selector**: 10, 25, 50, 100, 200 records per page
- **Page navigation**: First, Previous, Next, Last buttons
- **Page input**: Jump to specific page number
- **Total count**: Show "Showing X-Y of Z records"
- **Backend pagination**: API handles pagination for performance

### 4. Column Management
- **Show/Hide columns**: Checkbox list to toggle column visibility
- **Reorder columns**: Drag and drop to rearrange
- **Resize columns**: Drag column borders to resize
- **Pin columns**: Pin left/right for horizontal scrolling
- **Column presets**: Common column configurations

### 5. Row Selection
- **Select all**: Checkbox in header to select all visible rows
- **Select all pages**: Option to select all rows across all pages
- **Individual selection**: Checkbox per row
- **Range selection**: Shift + click to select range
- **Selection count**: Show "X of Y selected"
- **Persist selection**: Maintain selection across page changes

### 6. Mass Actions
- **Bulk edit**: Edit multiple records at once
- **Bulk delete**: Delete selected records with confirmation
- **Bulk export**: Export selected rows to CSV/Excel
- **Bulk tag**: Add/remove tags from selected records
- **Custom actions**: Module-specific bulk operations

### 7. Saved Views
- **Personal views**: Create custom table configurations
- **Shared views**: Share views with team/organization
- **Default view**: Set preferred view as default
- **View components**:
  - Columns (visibility, order, width)
  - Filters (active filters and values)
  - Sorting (columns and directions)
  - Grouping (if enabled)
- **Quick view switcher**: Dropdown to switch between views
- **View management**: Create, edit, duplicate, delete views

### 8. Tags & Labels
- **Inline tags**: Display tags in table cells
- **Tag filtering**: Filter by one or more tags
- **Bulk tag operations**: Add/remove tags from selection
- **Tag colors**: Visual distinction with color coding

### 9. Related Records
- **Lookup field display**: Show related record names as links
- **Hover preview**: Quick preview of related record on hover
- **Click to navigate**: Jump to related record detail page
- **Related count**: Show count of related records (e.g., "5 contacts")

### 10. Additional Features
- **Export**: CSV, Excel, PDF export with current filters
- **Row actions menu**: Edit, delete, duplicate, view per row
- **Responsive design**: Mobile-friendly table layout
- **Loading states**: Skeleton loaders during data fetch
- **Empty states**: User-friendly message when no data
- **Keyboard navigation**: Arrow keys, Enter, Escape shortcuts
- **Accessibility**: ARIA labels, screen reader support

---

## Technical Architecture

### Frontend Stack

**Libraries:**
- **@tanstack/svelte-table** v8 - Headless table logic
- **@dnd-kit/core** - Drag and drop for column reordering
- **date-fns** - Date parsing and formatting
- **lucide-svelte** - Icons
- **shadcn-svelte** components - UI primitives

**Components Structure:**
```
resources/js/components/datatable/
├── DataTable.svelte              # Main table component
├── DataTableHeader.svelte        # Column headers with sort/filter
├── DataTableRow.svelte           # Table row component
├── DataTableCell.svelte          # Cell renderer with type handling
├── DataTablePagination.svelte    # Pagination controls
├── DataTableToolbar.svelte       # Search, filters, actions bar
├── DataTableColumnHeader.svelte  # Sortable column header
├── DataTableFilter.svelte        # Filter popover
├── DataTableViewSwitcher.svelte  # Saved views dropdown
├── DataTableBulkActions.svelte   # Mass action buttons
├── filters/
│   ├── TextFilter.svelte         # Text filter component
│   ├── NumberFilter.svelte       # Number filter component
│   ├── DateFilter.svelte         # Date range filter
│   ├── SelectFilter.svelte       # Multi-select filter
│   └── LookupFilter.svelte       # Related record filter
├── columns/
│   ├── TextColumn.svelte         # Text cell renderer
│   ├── NumberColumn.svelte       # Formatted number display
│   ├── DateColumn.svelte         # Formatted date display
│   ├── BooleanColumn.svelte      # Checkbox/badge display
│   ├── LookupColumn.svelte       # Related record link
│   ├── TagsColumn.svelte         # Tags display with chips
│   └── ActionsColumn.svelte      # Row action menu
└── types.ts                       # TypeScript types
```

### Backend Architecture

**Database Tables:**

```sql
-- Saved table views
CREATE TABLE table_views (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    module_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT false,
    is_shared BOOLEAN DEFAULT false,
    config JSONB NOT NULL, -- { columns, filters, sorting, grouping }
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,

    INDEX idx_table_views_user_module (user_id, module_id),
    INDEX idx_table_views_shared (is_shared)
);

-- Shared view permissions
CREATE TABLE table_view_shares (
    id BIGSERIAL PRIMARY KEY,
    view_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,      -- NULL = shared with everyone
    team_id BIGINT UNSIGNED NULL,       -- Share with specific team
    can_edit BOOLEAN DEFAULT false,
    created_at TIMESTAMP,

    FOREIGN KEY (view_id) REFERENCES table_views(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_view_shares_view (view_id),
    INDEX idx_view_shares_user (user_id)
);
```

**API Endpoints:**

```
GET    /api/modules/{module}/records          # List records with filters
POST   /api/modules/{module}/records/export   # Export records
POST   /api/modules/{module}/records/bulk     # Bulk operations

GET    /api/modules/{module}/views            # List saved views
POST   /api/modules/{module}/views            # Create view
GET    /api/modules/{module}/views/{id}       # Get view config
PUT    /api/modules/{module}/views/{id}       # Update view
DELETE /api/modules/{module}/views/{id}       # Delete view
POST   /api/modules/{module}/views/{id}/share # Share view

GET    /api/modules/{module}/columns          # Get available columns
GET    /api/modules/{module}/filter-options   # Get filter options for column
```

**Request/Response Format:**

```typescript
// GET /api/modules/{module}/records
Request Query Parameters:
{
  page: 1,
  per_page: 50,
  sort: [
    { field: 'created_at', direction: 'desc' },
    { field: 'name', direction: 'asc' }
  ],
  filters: [
    { field: 'status', operator: 'in', value: ['active', 'pending'] },
    { field: 'created_at', operator: 'between', value: ['2024-01-01', '2024-12-31'] },
    { field: 'name', operator: 'contains', value: 'acme' }
  ],
  search: 'search term',
  columns: ['id', 'name', 'email', 'status', 'created_at']
}

Response:
{
  data: [
    {
      id: 1,
      name: "Acme Corp",
      email: "contact@acme.com",
      status: "active",
      created_at: "2024-01-15T10:00:00Z",
      _tags: [{ id: 1, name: "VIP", color: "#FF0000" }],
      _relationships: {
        contacts: { count: 5, preview: [...] }
      }
    }
  ],
  meta: {
    current_page: 1,
    from: 1,
    last_page: 10,
    per_page: 50,
    to: 50,
    total: 500
  }
}
```

---

## Component Props API

### DataTable.svelte

```typescript
interface DataTableProps {
  // Required
  moduleApiName: string;          // Module to display records from

  // Optional configuration
  defaultView?: number;            // Default view ID to load
  columns?: ColumnDef[];          // Column definitions (auto-generated if not provided)
  enableSelection?: boolean;      // Enable row selection (default: true)
  enableFilters?: boolean;        // Enable column filters (default: true)
  enableSearch?: boolean;         // Enable global search (default: true)
  enableSorting?: boolean;        // Enable sorting (default: true)
  enablePagination?: boolean;     // Enable pagination (default: true)
  enableViews?: boolean;          // Enable saved views (default: true)
  enableExport?: boolean;         // Enable export (default: true)
  enableBulkActions?: boolean;    // Enable bulk actions (default: true)

  // Callbacks
  onRowClick?: (row: any) => void;        // Row click handler
  onSelectionChange?: (rows: any[]) => void; // Selection change
  onBulkAction?: (action: string, rows: any[]) => void; // Bulk action
}

// Usage
<DataTable
  moduleApiName="contacts"
  defaultView={savedViewId}
  enableSelection={true}
  onRowClick={(row) => router.visit(`/contacts/${row.id}`)}
/>
```

---

## Implementation Phases

### Phase 1: Core Table (Week 1)
- [ ] Install TanStack Table and dependencies
- [ ] Create base DataTable component
- [ ] Implement column rendering with type detection
- [ ] Add sorting (single and multi-column)
- [ ] Add pagination controls
- [ ] Create backend API endpoint for listing records

### Phase 2: Filtering (Week 1-2)
- [ ] Create filter popover component
- [ ] Implement text filter
- [ ] Implement number filter
- [ ] Implement date range filter
- [ ] Implement select filter
- [ ] Add global search
- [ ] Update API to handle filters

### Phase 3: Selection & Bulk Actions (Week 2)
- [ ] Add row selection checkboxes
- [ ] Implement select all functionality
- [ ] Create bulk actions toolbar
- [ ] Add bulk delete
- [ ] Add bulk export
- [ ] Add bulk tag operations

### Phase 4: Column Management (Week 2)
- [ ] Create column visibility menu
- [ ] Add drag-drop column reordering
- [ ] Implement column resizing
- [ ] Add column pinning

### Phase 5: Saved Views (Week 3)
- [ ] Create table_views migration
- [ ] Build view CRUD API
- [ ] Create view management UI
- [ ] Implement view switcher
- [ ] Add view sharing functionality

### Phase 6: Polish & Advanced Features (Week 3)
- [ ] Add tags display and filtering
- [ ] Add related records with preview
- [ ] Implement keyboard navigation
- [ ] Add loading states and skeletons
- [ ] Mobile responsive design
- [ ] Accessibility improvements
- [ ] Write documentation

---

## Data Flow

```
User Interaction
    ↓
DataTable Component (State Management)
    ↓
    ├─→ Column Filters → Update URL Params → API Request
    ├─→ Sorting → Update URL Params → API Request
    ├─→ Pagination → Update URL Params → API Request
    ├─→ Global Search → Debounce → Update URL → API Request
    └─→ Saved View → Load Config → Update State → API Request
    ↓
Backend API
    ↓
    ├─→ Parse Query Parameters
    ├─→ Build Dynamic Query (Filters, Sorts, Pagination)
    ├─→ Apply Relationships (Eager Load)
    ├─→ Apply Tags (Join)
    └─→ Return JSON Response
    ↓
DataTable Component (Render)
    ↓
Display Data with Loading/Empty States
```

---

## State Management

Using Svelte 5 runes for reactive state:

```typescript
let tableState = $state({
  data: [],
  loading: false,
  pagination: {
    page: 1,
    perPage: 50,
    total: 0
  },
  sorting: [],
  filters: [],
  globalFilter: '',
  columnVisibility: {},
  columnOrder: [],
  rowSelection: {},
  currentView: null
});

// Derived state
let selectedRows = $derived(
  tableState.data.filter(row => tableState.rowSelection[row.id])
);

let hasActiveFilters = $derived(
  tableState.filters.length > 0 || tableState.globalFilter.length > 0
);
```

---

## URL State Sync

Keep table state in sync with URL for bookmarkable/shareable table states:

```typescript
// URL format: /modules/contacts?page=2&sort=name:asc&filter=status:active
function syncStateWithURL() {
  const params = new URLSearchParams();

  params.set('page', tableState.pagination.page);
  params.set('per_page', tableState.pagination.perPage);

  if (tableState.sorting.length) {
    params.set('sort', tableState.sorting.map(s =>
      `${s.field}:${s.direction}`
    ).join(','));
  }

  if (tableState.filters.length) {
    params.set('filters', JSON.stringify(tableState.filters));
  }

  if (tableState.globalFilter) {
    params.set('search', tableState.globalFilter);
  }

  router.visit(`?${params.toString()}`, { preserveState: true });
}
```

---

## Performance Optimizations

1. **Virtual Scrolling**: For tables with 1000+ rows visible
2. **Debounced Search**: 300ms delay on global search
3. **Memoized Filters**: Cache filter results per column
4. **Lazy Loading**: Load column filters on demand
5. **Request Cancellation**: Cancel in-flight requests when filters change
6. **Backend Pagination**: Always paginate on server for large datasets
7. **Index Optimization**: Database indexes on commonly filtered columns

---

## Accessibility

- **Keyboard Navigation**:
  - Tab: Navigate through interactive elements
  - Arrow keys: Navigate cells
  - Space: Toggle selection
  - Enter: Open row
  - Escape: Close popovers

- **Screen Reader Support**:
  - ARIA labels on all controls
  - Role="table", "row", "cell" attributes
  - Announce selection changes
  - Announce sort direction

- **Focus Management**:
  - Visible focus indicators
  - Focus trap in modals/popovers
  - Return focus after actions

---

## Testing Strategy

**Unit Tests:**
- Column filter logic
- Sort direction toggling
- Pagination calculation
- URL state serialization

**Component Tests:**
- DataTable renders correctly
- Sorting updates UI and API
- Filters apply correctly
- Selection works across pages

**Integration Tests:**
- Full table flow with API
- Saved views CRUD
- Bulk actions execute
- Export generates file

**E2E Tests:**
- User can filter, sort, paginate
- User can save and load views
- User can perform bulk actions
- Table works on mobile

---

## Security Considerations

1. **Authorization**: Check user permissions for:
   - View module records
   - Edit records (for bulk actions)
   - Delete records (for bulk delete)
   - Share views (for view sharing)

2. **Input Validation**:
   - Sanitize filter values
   - Validate sort columns against schema
   - Limit pagination size (max 200)
   - Escape search terms

3. **Rate Limiting**: API endpoints for:
   - Record listing (100 req/min)
   - Export (10 req/min)
   - Bulk actions (20 req/min)

4. **Audit Logging**: Log bulk actions with:
   - User ID
   - Action type
   - Affected record IDs
   - Timestamp

---

## Success Metrics

- **Performance**: Table loads in < 500ms with 1000 records
- **Usability**: Users can find and filter records in < 3 clicks
- **Adoption**: 90% of users use at least one saved view
- **Reusability**: Component used in 10+ different modules
- **Accessibility**: WCAG 2.1 AA compliant

---

## Future Enhancements

- **Grouping**: Group rows by column value
- **Aggregations**: Show sum/avg/count in footer
- **Inline editing**: Edit cells directly in table
- **Frozen columns**: Keep first N columns visible while scrolling
- **Row expansion**: Expandable rows for nested data
- **Advanced export**: Export with custom templates
- **Scheduled exports**: Email CSV daily/weekly
- **Custom columns**: User-defined computed columns
- **Conditional formatting**: Color rows/cells based on rules
- **Chart view**: Pivot table data into charts

---

## Documentation

Create comprehensive documentation:
- Component API reference
- Usage examples for each feature
- Backend API specification
- Customization guide
- Troubleshooting guide
- Migration guide for existing tables
