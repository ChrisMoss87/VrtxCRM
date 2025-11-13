<script lang="ts">
	import { getContext } from 'svelte';
	import { Checkbox } from '@/components/ui/checkbox';
	import { Button } from '@/components/ui/button';
	import * as Popover from '@/components/ui/popover';
	import { ArrowUp, ArrowDown, ChevronsUpDown, Filter } from 'lucide-svelte';
	import { Badge } from '@/components/ui/badge';
	import type { ColumnDef, TableContext } from './types';
	import { TextFilter, NumberFilter, DateFilter, SelectFilter } from './filters';

	interface Props {
		columns: ColumnDef[];
		enableSelection?: boolean;
		enableSorting?: boolean;
		enableFilters?: boolean;
	}

	let {
		columns,
		enableSelection = true,
		enableSorting = true,
		enableFilters = true
	}: Props = $props();

	const table = getContext<TableContext>('table');

	// Track which filter popovers are open
	let filterOpen = $state<Record<string, boolean | undefined>>({});

	// Check if all rows are selected
	let allSelected = $derived(
		table.state.data.length > 0 &&
			table.state.data.every((row) => table.state.rowSelection[row.id])
	);

	// Check if some rows are selected
	let someSelected = $derived(
		table.state.data.some((row) => table.state.rowSelection[row.id]) && !allSelected
	);

	// Get sort info for column
	function getSortInfo(columnId: string) {
		const sortIndex = table.state.sorting.findIndex((s) => s.field === columnId);

		if (sortIndex === -1) {
			return { isSorted: false, direction: null, priority: null };
		}

		return {
			isSorted: true,
			direction: table.state.sorting[sortIndex].direction,
			priority: table.state.sorting.length > 1 ? sortIndex + 1 : null
		};
	}

	// Handle column header click
	function handleHeaderClick(column: ColumnDef, event: MouseEvent) {
		if (!enableSorting || !column.sortable) return;

		table.updateSort(column.id, event.shiftKey);
	}

	// Check if column has active filter
	function hasActiveFilter(columnId: string): boolean {
		return table.state.filters.some(f => f.field === columnId);
	}

	// Get active filter for column
	function getActiveFilter(columnId: string) {
		return table.state.filters.find(f => f.field === columnId);
	}

	// Handle filter apply
	function handleFilterApply(columnId: string, filter: any) {
		if (filter) {
			table.updateFilter({
				field: columnId,
				operator: filter.operator,
				value: filter.value
			});
		} else {
			table.removeFilter(columnId);
		}
		filterOpen[columnId] = false;
	}

	// Determine filter type from column type
	function getFilterType(column: ColumnDef): 'text' | 'number' | 'date' | 'select' {
		if (column.type === 'number' || column.type === 'decimal' || column.type === 'currency' || column.type === 'percent') {
			return 'number';
		}
		if (column.type === 'date' || column.type === 'datetime') {
			return 'date';
		}
		if (column.type === 'select' || column.type === 'radio' || column.type === 'multiselect') {
			return 'select';
		}
		return 'text';
	}
</script>

<thead class="[&_tr]:border-b">
	<tr class="border-b transition-colors hover:bg-muted/50">
		<!-- Selection checkbox -->
		{#if enableSelection}
			<th class="h-12 w-12 px-4">
				<div class="flex items-center justify-center">
					<Checkbox
						checked={allSelected}
						indeterminate={someSelected}
						onCheckedChange={() => table.toggleAllRows()}
						aria-label="Select all rows"
					/>
				</div>
			</th>
		{/if}

		<!-- Column headers -->
		{#each columns as column (column.id)}
			{@const sortInfo = getSortInfo(column.id)}
			<th
				class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0"
				style:width={table.state.columnWidths[column.id]
					? `${table.state.columnWidths[column.id]}px`
					: undefined}
			>
				<div class="flex items-center gap-1">
					<button
						class="flex items-center gap-2 hover:text-foreground {column.sortable && enableSorting
							? 'cursor-pointer'
							: 'cursor-default'}"
						onclick={(e) => handleHeaderClick(column, e)}
						disabled={!column.sortable || !enableSorting}
					>
						<span>{column.header}</span>

						{#if column.sortable && enableSorting}
							<div class="flex h-4 w-4 items-center justify-center">
								{#if sortInfo.isSorted}
									{#if sortInfo.direction === 'asc'}
										<ArrowUp class="h-3.5 w-3.5" />
									{:else}
										<ArrowDown class="h-3.5 w-3.5" />
									{/if}
									{#if sortInfo.priority !== null}
										<span class="ml-1 text-xs">{sortInfo.priority}</span>
									{/if}
								{:else}
									<ChevronsUpDown class="h-3.5 w-3.5 opacity-50" />
								{/if}
							</div>
						{/if}
					</button>

					{#if enableFilters && column.filterable !== false}
						{@const filterType = getFilterType(column)}
						{@const activeFilter = getActiveFilter(column.id)}

						<Popover.Root
							open={filterOpen[column.id] ?? false}
							onOpenChange={(open) => (filterOpen[column.id] = open)}
						>
							<Popover.Trigger asChild>
								{#snippet child({ props })}
									<Button
										{...props}
										variant="ghost"
										size="icon"
										class="h-6 w-6 {hasActiveFilter(column.id) ? 'text-primary' : 'text-muted-foreground'}"
									>
										<Filter class="h-3.5 w-3.5" />
										{#if hasActiveFilter(column.id)}
											<Badge variant="secondary" class="ml-0.5 h-4 w-4 rounded-full p-0 text-[10px]">
												1
											</Badge>
										{/if}
									</Button>
								{/snippet}
							</Popover.Trigger>
							<Popover.Content align="start" class="p-0 w-auto">
								{#if filterType === 'text'}
									<TextFilter
										field={column.id}
										initialValue={activeFilter}
										onApply={(filter) => handleFilterApply(column.id, filter)}
										onClose={() => (filterOpen[column.id] = false)}
									/>
								{:else if filterType === 'number'}
									<NumberFilter
										field={column.id}
										initialValue={activeFilter}
										onApply={(filter) => handleFilterApply(column.id, filter)}
										onClose={() => (filterOpen[column.id] = false)}
									/>
								{:else if filterType === 'date'}
									<DateFilter
										field={column.id}
										initialValue={activeFilter}
										onApply={(filter) => handleFilterApply(column.id, filter)}
										onClose={() => (filterOpen[column.id] = false)}
									/>
								{:else if filterType === 'select'}
									<SelectFilter
										field={column.id}
										options={column.filterOptions || []}
										initialValue={activeFilter}
										onApply={(filter) => handleFilterApply(column.id, filter)}
										onClose={() => (filterOpen[column.id] = false)}
									/>
								{/if}
							</Popover.Content>
						</Popover.Root>
					{/if}
				</div>
			</th>
		{/each}
	</tr>
</thead>
