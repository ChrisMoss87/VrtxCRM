<script lang="ts">
	import { router } from '@inertiajs/svelte';
	import {
		Breadcrumb,
		BreadcrumbItem,
		BreadcrumbLink,
		BreadcrumbList,
		BreadcrumbPage,
		BreadcrumbSeparator,
	} from '@/components/ui/breadcrumb';
	import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
	import DynamicForm from '@/components/modules/DynamicForm.svelte';
	import type { Module, ModuleRecord } from '@/types/modules';

	interface Props {
		module: Module;
		record: ModuleRecord;
		errors?: Record<string, string>;
	}

	let { module, record, errors = {} }: Props = $props();

	let isSubmitting = $state(false);

	function handleSubmit(data: Record<string, any>) {
		isSubmitting = true;

		router.put(`/api/modules/${module.api_name}/records/${record.id}`, data, {
			onSuccess: () => {
				router.visit(`/modules/${module.api_name}/${record.id}`);
			},
			onError: () => {
				isSubmitting = false;
			},
			onFinish: () => {
				isSubmitting = false;
			},
		});
	}

	function handleCancel() {
		router.visit(`/modules/${module.api_name}/${record.id}`);
	}

	// Get display name for record
	const recordName = $derived(() => {
		if (!module.blocks?.length) return `Record #${record.id}`;

		const firstTextField = module.blocks[0]?.fields?.find(
			(f) => f.type === 'text' && record.data[f.api_name]
		);

		return firstTextField
			? String(record.data[firstTextField.api_name])
			: `Record #${record.id}`;
	});
</script>

<svelte:head>
	<title>Edit {recordName()} | {module.name} | VrtxCRM</title>
</svelte:head>

<AppLayout>
	<div class="flex flex-col gap-6">
		<!-- Breadcrumb -->
		<Breadcrumb>
			<BreadcrumbList>
				<BreadcrumbItem>
					<BreadcrumbLink href="/">Dashboard</BreadcrumbLink>
				</BreadcrumbItem>
				<BreadcrumbSeparator />
				<BreadcrumbItem>
					<BreadcrumbLink href={`/modules/${module.api_name}`}>
						{module.name}
					</BreadcrumbLink>
				</BreadcrumbItem>
				<BreadcrumbSeparator />
				<BreadcrumbItem>
					<BreadcrumbLink href={`/modules/${module.api_name}/${record.id}`}>
						{recordName()}
					</BreadcrumbLink>
				</BreadcrumbItem>
				<BreadcrumbSeparator />
				<BreadcrumbItem>
					<BreadcrumbPage>Edit</BreadcrumbPage>
				</BreadcrumbItem>
			</BreadcrumbList>
		</Breadcrumb>

		<!-- Header -->
		<div>
			<h1 class="text-3xl font-bold tracking-tight">Edit {recordName()}</h1>
			<p class="text-muted-foreground">
				Update {module.name.toLowerCase().replace(/s$/, '')} information
			</p>
		</div>

		<!-- Form -->
		<DynamicForm
			{module}
			initialData={record}
			onSubmit={handleSubmit}
			onCancel={handleCancel}
			{isSubmitting}
			{errors}
		/>
	</div>
</AppLayout>
