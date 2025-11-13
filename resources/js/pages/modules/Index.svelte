<script lang="ts">
	import { router } from '@inertiajs/svelte';
	import { Button } from '@/components/ui/button';
	import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
	import { DataTable } from '@/components/datatable';
	import { generateColumnsFromModule } from '@/components/datatable/utils';
	import type { Module } from '@/types/modules';
	import { Plus } from 'lucide-svelte';

	interface Props {
		module: Module;
		defaultViewId?: number | null;
	}

	let { module, defaultViewId }: Props = $props();

	const columns = generateColumnsFromModule(module);

	function handleCreateNew() {
		router.visit(`/modules/${module.api_name}/create`);
	}

	function handleRowClick(row: any) {
		router.visit(`/modules/${module.api_name}/${row.id}`);
	}
</script>

<svelte:head>
	<title>{module.name} | VrtxCRM</title>
</svelte:head>

<AppLayout>
	<div class="flex flex-col gap-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-3xl font-bold tracking-tight">{module.name}</h1>
				<p class="text-muted-foreground">
					Manage your {module.name.toLowerCase()}
				</p>
			</div>

			<Button onclick={handleCreateNew}>
				<Plus class="mr-2 h-4 w-4" />
				New {module.name.replace(/s$/, '')}
			</Button>
		</div>

		<!-- DataTable -->
		<DataTable
			moduleApiName={module.api_name}
			{columns}
			defaultView={defaultViewId}
			enableSelection={true}
			enableSorting={true}
			enableSearch={true}
			enableFilters={true}
			enableBulkActions={true}
			enableExport={true}
			enableViews={true}
			onRowClick={handleRowClick}
		/>
	</div>
</AppLayout>
