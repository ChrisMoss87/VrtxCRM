# Sprint 5 Issues and Recommended Fixes

This document provides detailed information about each issue found and recommended fixes.

---

## CRITICAL ISSUES

### Issue #1: Missing CheckboxField and SwitchField Components

**File**: `resources/js/components/modules/DynamicForm.svelte`
**Lines**: 39-67 (getFieldComponent function), specifically lines 61 and 63

**Current Code**:
```typescript
function getFieldComponent(fieldType: string) {
	switch (fieldType) {
		case 'text':
		// ... other cases ...
		case 'checkbox':
			return CheckboxField;  // ← NOT IMPORTED/DEFINED
		case 'toggle':
			return SwitchField;    // ← NOT IMPORTED/DEFINED
		default:
			return TextField;
	}
}
```

**Problem**:
- `CheckboxField` is never imported and doesn't exist in the codebase
- `SwitchField` is never imported and doesn't exist in the codebase
- These components are referenced on lines 61 and 63 but not defined anywhere
- When a form contains checkbox or toggle fields, the app will crash with `ReferenceError`

**Impact**: Any module with checkbox or toggle fields will crash when trying to create/edit records.

**Solutions** (Choose One):

**Option A: Create the Missing Components**
Create `resources/js/components/form/CheckboxField.svelte`:
```svelte
<script lang="ts">
	import FieldBase from './FieldBase.svelte';
	import { Checkbox } from '@/components/ui/checkbox';

	interface Props {
		label?: string;
		name: string;
		value?: boolean;
		description?: string;
		error?: string;
		required?: boolean;
		disabled?: boolean;
		width?: 25 | 50 | 75 | 100;
		class?: string;
		onchange?: (value: boolean) => void;
	}

	let {
		label,
		name,
		value = $bindable(false),
		description,
		error,
		required = false,
		disabled = false,
		width = 100,
		class: className,
		onchange
	}: Props = $props();

	function handleChange(e: Event) {
		const target = e.target as HTMLInputElement;
		value = target.checked;
		onchange?.(value);
	}
</script>

<FieldBase {label} {name} {description} {error} {required} {disabled} {width} class={className}>
	{#snippet children(props)}
		<div class="flex items-center space-x-2">
			<Checkbox
				id={name}
				{...props}
				checked={value}
				onchange={handleChange}
				{disabled}
			/>
			{#if label}
				<label for={name} class="text-sm font-medium">
					{label}
					{#if required}
						<span class="text-destructive">*</span>
					{/if}
				</label>
			{/if}
		</div>
	{/snippet}
</FieldBase>
```

Create `resources/js/components/form/SwitchField.svelte`:
```svelte
<script lang="ts">
	import FieldBase from './FieldBase.svelte';
	import { Switch } from '@/components/ui/switch';

	interface Props {
		label?: string;
		name: string;
		value?: boolean;
		description?: string;
		error?: string;
		required?: boolean;
		disabled?: boolean;
		width?: 25 | 50 | 75 | 100;
		class?: string;
		onchange?: (value: boolean) => void;
	}

	let {
		label,
		name,
		value = $bindable(false),
		description,
		error,
		required = false,
		disabled = false,
		width = 100,
		class: className,
		onchange
	}: Props = $props();

	function handleChange(e: Event) {
		const target = e.target as HTMLInputElement;
		value = target.checked;
		onchange?.(value);
	}
</script>

<FieldBase {label} {name} {description} {error} {required} {disabled} {width} class={className}>
	{#snippet children(props)}
		<div class="flex items-center space-x-2">
			<Switch
				id={name}
				{...props}
				checked={value}
				onchange={handleChange}
				{disabled}
			/>
			{#if label}
				<label for={name} class="text-sm font-medium">
					{label}
					{#if required}
						<span class="text-destructive">*</span>
					{/if}
				</label>
			{/if}
		</div>
	{/snippet}
</FieldBase>
```

Then update `resources/js/components/form/index.ts`:
```typescript
export { default as FieldBase } from './FieldBase.svelte';
export { default as TextField } from './TextField.svelte';
export { default as TextareaField } from './TextareaField.svelte';
export { default as SelectField } from './SelectField.svelte';
export { default as CheckboxField } from './CheckboxField.svelte';  // ADD THIS
export { default as SwitchField } from './SwitchField.svelte';      // ADD THIS
```

Finally, update `resources/js/components/modules/DynamicForm.svelte` imports:
```typescript
import { CheckboxField, TextField, TextareaField, SelectField, SwitchField } from '@/components/form';
```

**Option B: Implement Inline in DynamicForm**
Keep the checkbox/toggle handling as inline code in DynamicForm.svelte (already partially done on lines 124-166).
This is less clean but works for simple cases.

**Option C: Remove These Field Types**
Don't support checkbox and toggle fields in the dynamic form system.

---

### Issue #2: Missing Select.Value Export

**File**: `resources/js/components/form/SelectField.svelte`
**Line**: 52

**Current Code**:
```svelte
<Select.Root selected={{ value, label: options.find(o => o.value === value)?.label ?? placeholder }} onSelectedChange={(selected) => handleValueChange(selected?.value)}>
	<Select.Trigger {...props}>
		<Select.Value {placeholder} />  <!-- ERROR: Select.Value doesn't exist -->
	</Select.Trigger>
	<Select.Content>
		{#each options as option}
			<Select.Item value={option.value}>
				{option.label}
			</Select.Item>
		{/each}
	</Select.Content>
</Select.Root>
```

**Error**:
```
"Value" is not exported by "resources/js/components/ui/select/index.ts"
```

**Problem**:
- The `Select.Value` component is used but not exported from the select component index
- The bits-ui library provides this component but it's not re-exported

**Solution**:
Update `resources/js/components/ui/select/index.ts`:

```typescript
import { Select as SelectPrimitive } from "bits-ui";

import Group from "./select-group.svelte";
import Label from "./select-label.svelte";
import Item from "./select-item.svelte";
import Content from "./select-content.svelte";
import Trigger from "./select-trigger.svelte";
import Separator from "./select-separator.svelte";
import ScrollDownButton from "./select-scroll-down-button.svelte";
import ScrollUpButton from "./select-scroll-up-button.svelte";
import GroupHeading from "./select-group-heading.svelte";

const Root = SelectPrimitive.Root;
const Value = SelectPrimitive.Value;  // ADD THIS LINE

export {
	Root,
	Group,
	Label,
	Item,
	Content,
	Trigger,
	Separator,
	ScrollDownButton,
	ScrollUpButton,
	GroupHeading,
	Value,  // ADD TO EXPORTS
	//
	Root as Select,
	Group as SelectGroup,
	Label as SelectLabel,
	Item as SelectItem,
	Content as SelectContent,
	Trigger as SelectTrigger,
	Separator as SelectSeparator,
	ScrollDownButton as SelectScrollDownButton,
	ScrollUpButton as SelectScrollUpButton,
	GroupHeading as SelectGroupHeading,
	Value as SelectValue,  // ADD TO EXPORTS
};
```

---

## HIGH PRIORITY ISSUES

### Issue #3: Null/Undefined Date Handling

**File**: `resources/js/components/modules/FieldValue.svelte`
**Lines**: 42-50

**Current Code**:
```svelte
{:else if field.type === 'date'}
	{new Date(value).toLocaleDateString()}
{:else if field.type === 'datetime'}
	{new Date(value).toLocaleString()}
{:else if field.type === 'time'}
	{new Date(`2000-01-01T${value}`).toLocaleTimeString([], {
		hour: '2-digit',
		minute: '2-digit',
	})}
```

**Problem**:
- The outer `if` on line 14 checks for `value === null || value === undefined || value === ''`
- However, if a date string is invalid or malformed, it will create an `Invalid Date` object
- The display will show "Invalid Date" instead of "Not set"
- For time fields, if value is null/undefined, the string concatenation creates `"2000-01-01Tundefined"`

**Impact**: Poor user experience. Users see "Invalid Date" message instead of a clean "Not set" indicator.

**Solution**:
Add helper function to validate dates:

```typescript
function isValidDate(value: any): boolean {
	if (!value) return false;
	const date = new Date(value);
	return !isNaN(date.getTime());
}
```

Then use it in the template:

```svelte
{:else if field.type === 'date'}
	{#if isValidDate(value)}
		{new Date(value).toLocaleDateString()}
	{:else}
		<span class="text-muted-foreground italic">Not set</span>
	{/if}
{:else if field.type === 'datetime'}
	{#if isValidDate(value)}
		{new Date(value).toLocaleString()}
	{:else}
		<span class="text-muted-foreground italic">Not set</span>
	{/if}
{:else if field.type === 'time'}
	{#if isValidDate(`2000-01-01T${value}`)}
		{new Date(`2000-01-01T${value}`).toLocaleTimeString([], {
			hour: '2-digit',
			minute: '2-digit',
		})}
	{:else}
		<span class="text-muted-foreground italic">Not set</span>
	{/if}
```

---

### Issue #4: Missing Keys in Each Loops

**Files**:
- `resources/js/components/form/SelectField.svelte` - Line 55
- `resources/js/components/modules/FieldValue.svelte` - Line 84

**Problem**:
When you use `{#each}` loops in Svelte, it's best practice (and required for proper reactivity with transitions) to provide a unique key for each item.

**Current Code (SelectField.svelte:55)**:
```svelte
{#each options as option}
	<Select.Item value={option.value}>
		{option.label}
	</Select.Item>
{/each}
```

**Fixed Code**:
```svelte
{#each options as option (option.value)}
	<Select.Item value={option.value}>
		{option.label}
	</Select.Item>
{/each}
```

**Current Code (FieldValue.svelte:84)**:
```svelte
{#each value as val}
	{@const opt = field.options?.find((o) => o.value === val)}
	<Badge style={opt?.color ? `background-color: ${opt.color}` : ''}>
		{opt?.label || val}
	</Badge>
{/each}
```

**Fixed Code**:
```svelte
{#each value as val (val)}
	{@const opt = field.options?.find((o) => o.value === val)}
	<Badge style={opt?.color ? `background-color: ${opt.color}` : ''}>
		{opt?.label || val}
	</Badge>
{/each}
```

---

### Issue #5: Deprecated svelte:component Usage

**File**: `resources/js/components/modules/ModuleTable.svelte`
**Line**: 87

**Current Code**:
```svelte
<svelte:component
	this={getSortIcon(field.api_name)}
	class="h-4 w-4"
/>
```

**Warning**: `<svelte:component>` is deprecated in Svelte 5 runes mode. Components are dynamic by default.

**Solution**:
```typescript
function getSortIcon(field: string) {
	if (sortBy !== field) return ArrowUpDown;
	return sortDirection === 'asc' ? ArrowUp : ArrowDown;
}
```

Then use the component directly:
```svelte
{@const Icon = getSortIcon(field.api_name)}
<Icon class="h-4 w-4" />
```

---

## MEDIUM PRIORITY ISSUES

### Issue #6: Case Block Declaration in ModuleTable.svelte

**File**: `resources/js/components/modules/ModuleTable.svelte`
**Line**: 62

**Current Code**:
```javascript
function formatValue(value: any, field: Field): string {
	// ...
	case 'select':
	case 'radio':
		const option = field.options?.find((opt) => opt.value === value);  // ← Issue
		return option?.label ?? value;
```

**Problem**: JavaScript doesn't allow lexical declarations (`const`) directly in case blocks without braces.

**Solution**:
```javascript
case 'select':
case 'radio': {
	const option = field.options?.find((opt) => opt.value === value);
	return option?.label ?? value;
}
```

---

### Issue #7: Missing Navigation resolve() in FieldValue.svelte

**File**: `resources/js/components/modules/FieldValue.svelte`
**Line**: 34

**Current Code**:
```svelte
<a
	href={value}
	target="_blank"
	rel="noopener noreferrer"
	class="inline-flex items-center gap-1 text-primary hover:underline"
>
```

**Warning**: Svelte 5 recommends using `resolve()` for dynamic URLs for better type safety.

**Solution**:
This is primarily a linting warning. The code is functionally safe due to the `href` binding preventing injection attacks. If you want to silence the warning:

```svelte
<a
	href={value as string}
	target="_blank"
	rel="noopener noreferrer"
	class="inline-flex items-center gap-1 text-primary hover:underline"
>
```

Or use resolve() if it's available in your SvelteKit project.

---

## CODE QUALITY ISSUES

### Issue #8: Prettier Formatting

**Files that need formatting**:
1. `resources/css/app.css`
2. `resources/js/components/form/index.ts`
3. `resources/js/hooks/is-mobile.svelte.ts`
4. `resources/js/lib/utils.ts`
5. `resources/js/types/modules.d.ts`

**Fix**: Run Prettier formatter
```bash
npm run format
```

---

### Issue #9: SQL Injection Risk in Sort Parameter

**File**: `resources/js/pages/modules/ModuleViewController.php`
**Line**: 38

**Current Code**:
```php
if ($sortBy) {
	$query->orderByRaw("data->>'$.{$sortBy}' {$sortDirection}");
}
```

**Problem**: The `$sortBy` parameter comes directly from user input without validation. Although it's used in a query that's unlikely to be vulnerable, it's better to validate it against allowed fields.

**Solution**:
```php
if ($sortBy) {
	// Validate that sortBy is an actual field in the module
	$allowedFields = $module->blocks
		->flatMap(fn($block) => $block->fields->pluck('api_name'))
		->toArray();

	if (in_array($sortBy, $allowedFields)) {
		$query->orderByRaw("data->>'$.{$sortBy}' {$sortDirection}");
	}
}
```

---

## TESTING FAILURES

### Issue #10: Failing Test - TenantIsolationTest

**File**: `tests/Feature/Tenancy/TenantIsolationTest.php`
**Error**:
```
SQLSTATE[23502]: Not null violation: 7 ERROR: null value in column "api_name"
of relation "modules" violates not-null constraint
```

**Problem**: When creating a test module, the `api_name` field is not being set, but it's required in the database.

**Solution**: Ensure the module factory or seeder sets `api_name`:

```php
// In your module factory or seeder
Module::factory()->create([
	'name' => 'Custom Module for Tenant 1',
	'api_name' => 'custom_module_for_tenant_1', // Add this
	'is_active' => true,
]);
```

---

## SECURITY CONSIDERATIONS

### Evaluated and Safe:
- XSS Protection: Svelte's reactive binding properly escapes values
- URL Field Handling: Uses safe href binding, no JavaScript protocol execution
- Form Submission: POST/PUT requests properly validated on backend
- Field Data: All user input validated by backend validation rules

### Recommendations:
- Implement Content Security Policy (CSP) headers
- Add CSRF protection to form submissions (already done via Inertia)
- Validate color values in badge styling to prevent CSS injection
- Sanitize user input on backend before database storage

---

## SUMMARY TABLE

| Issue | File | Line | Severity | Fix Time |
|-------|------|------|----------|----------|
| Missing CheckboxField | DynamicForm.svelte | 61 | CRITICAL | 30 min |
| Missing SwitchField | DynamicForm.svelte | 63 | CRITICAL | 30 min |
| Missing Select.Value | SelectField.svelte | 52 | CRITICAL | 5 min |
| Null date handling | FieldValue.svelte | 43-47 | HIGH | 15 min |
| Missing each keys | Multiple | Various | HIGH | 10 min |
| svelte:component deprecated | ModuleTable.svelte | 87 | MEDIUM | 5 min |
| Case block syntax | ModuleTable.svelte | 62 | MEDIUM | 5 min |
| Navigation resolve | FieldValue.svelte | 34 | LOW | 5 min |
| Prettier formatting | 5 files | Various | LOW | 1 min |
| SQL injection risk | ModuleViewController.php | 38 | HIGH | 15 min |
| Test api_name missing | TenantIsolationTest.php | 123 | MEDIUM | 10 min |

---

## ESTIMATED COMPLETION TIME

- **Critical Issues**: 1 hour
- **High Priority Issues**: 45 minutes
- **Medium Priority Issues**: 30 minutes
- **Testing & Verification**: 1-2 hours

**Total: 3.5-4.5 hours**

---

## NEXT STEPS

1. Implement the missing field components (CheckboxField, SwitchField)
2. Add Select.Value export
3. Fix date handling with validation
4. Add keys to each loops
5. Update deprecated svelte:component syntax
6. Run npm run format to fix formatting
7. Add field validation to ModuleViewController
8. Fix failing test
9. Run full test suite
10. Deploy and verify in staging environment

