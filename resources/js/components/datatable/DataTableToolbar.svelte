<script lang="ts">
	import { getContext } from 'svelte';
	import { Button } from '@/components/ui/button';
	import { Input } from '@/components/ui/input';
	import { Search, X, Download, Trash2, Tag } from 'lucide-svelte';
	import type { TableContext } from './types';
	import DataTableViewSwitcher from './DataTableViewSwitcher.svelte';
	import DataTableColumnToggle from './DataTableColumnToggle.svelte';
	import DataTableSaveViewDialog from './DataTableSaveViewDialog.svelte';
	import DataTableFilterChips from './DataTableFilterChips.svelte';

	interface Props {
		enableSearch?: boolean;
		enableFilters?: boolean;
		enableBulkActions?: boolean;
		enableExport?: boolean;
		enableViews?: boolean;
		enableColumnToggle?: boolean;
		module?: string;
		defaultViewId?: number | null;
		selectedCount?: number;
		hasFilters?: boolean;
	}

	let {
		enableSearch = true,
		enableFilters = true,
		enableBulkActions = true,
		enableExport = true,
		enableViews = true,
		enableColumnToggle = true,
		module = '',
		defaultViewId,
		selectedCount = 0,
		hasFilters = false
	}: Props = $props();

	const table = getContext<TableContext>('table');

	let searchValue = $state(table.state.globalFilter);
	let currentView = $state<any>(null);
	let saveViewDialogOpen = $state(false);

	function handleSearchInput(event: Event) {
		const target = event.target as HTMLInputElement;
		searchValue = target.value;
		table.updateGlobalFilter(target.value);
	}

	function clearSearch() {
		searchValue = '';
		table.updateGlobalFilter('');
	}

	async function handleViewChange(view: any) {
		currentView = view;
		await table.loadView(view);
	}

	function handleSaveView() {
		if (currentView) {
			// Update existing view
			updateCurrentView();
		} else {
			// Open dialog to create new view
			saveViewDialogOpen = true;
		}
	}

	async function updateCurrentView() {
		if (!currentView) return;

		try {
			const response = await fetch(`/api/table-views/${currentView.id}`, {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
				},
				body: JSON.stringify({
					filters: table.state.filters || null,
					sorting: table.state.sorting || null,
					column_visibility: table.state.columnVisibility || null,
					page_size: table.state.pagination.perPage || 50
				})
			});

			if (response.ok) {
				console.log('View updated successfully');
			}
		} catch (error) {
			console.error('Failed to update view:', error);
		}
	}

	function getCurrentTableState() {
		return {
			filters: table.state.filters,
			sorting: table.state.sorting,
			columnVisibility: table.state.columnVisibility,
			pageSize: table.state.pagination.perPage
		};
	}
</script>

<div class="flex flex-col gap-2">
	<!-- Top row: View switcher and actions -->
	<div class="flex items-center justify-between">
		<div class="flex items-center gap-2">
			{#if enableViews && module}
				<DataTableViewSwitcher
					{module}
					{defaultViewId}
					bind:currentView
					onViewChange={handleViewChange}
					onSaveView={handleSaveView}
					onCreateView={() => (saveViewDialogOpen = true)}
				/>
			{/if}
		</div>

		<div class="flex items-center gap-2">
			{#if enableColumnToggle}
				<DataTableColumnToggle />
			{/if}

			{#if enableExport && selectedCount === 0}
				<Button variant="outline" size="sm">
					<Download class="mr-2 h-4 w-4" />
					Export
				</Button>
			{/if}
		</div>
	</div>

	<!-- Filter chips row (if any filters active) -->
	{#if table.state.filters.length > 0}
		<DataTableFilterChips />
	{/if}

	<!-- Bottom row: Search, filters, and bulk actions -->
	<div class="flex items-center justify-between">
		<!-- Left side: Search and filters -->
		<div class="flex flex-1 items-center gap-2">
			{#if enableSearch}
				<div class="relative w-full max-w-sm">
					<Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
					<Input
						type="search"
						placeholder="Search..."
						value={searchValue}
						oninput={handleSearchInput}
						class="pl-8 pr-8"
					/>
					{#if searchValue}
						<button
							onclick={clearSearch}
							class="absolute right-2.5 top-2.5 text-muted-foreground hover:text-foreground"
						>
							<X class="h-4 w-4" />
						</button>
					{/if}
				</div>
			{/if}

			{#if hasFilters}
				<Button variant="ghost" size="sm" onclick={() => table.clearFilters()}>
					<X class="mr-2 h-4 w-4" />
					Clear filters
				</Button>
			{/if}
		</div>

		<!-- Right side: Bulk actions -->
		<div class="flex items-center gap-2">
			{#if selectedCount > 0 && enableBulkActions}
				<div class="flex items-center gap-2">
					<span class="text-sm text-muted-foreground">
						{selectedCount} selected
					</span>

					<Button variant="outline" size="sm">
						<Tag class="mr-2 h-4 w-4" />
						Add tags
					</Button>

					<Button variant="outline" size="sm">
						<Download class="mr-2 h-4 w-4" />
						Export
					</Button>

					<Button variant="destructive" size="sm">
						<Trash2 class="mr-2 h-4 w-4" />
						Delete
					</Button>

					<Button variant="ghost" size="sm" onclick={() => table.clearSelection()}>
						<X class="h-4 w-4" />
					</Button>
				</div>
			{/if}
		</div>
	</div>
</div>

{#if module}
	<DataTableSaveViewDialog
		bind:open={saveViewDialogOpen}
		{module}
		currentState={getCurrentTableState()}
		onSaved={() => {
			// Refresh the view switcher
			currentView = null;
		}}
	/>
{/if}
