<!--<script lang="ts">-->
<!--	import { router } from '@inertiajs/svelte';-->
<!--	import { toast } from 'svelte-sonner';-->
<!--	import {-->
<!--		Breadcrumb,-->
<!--		BreadcrumbItem,-->
<!--		BreadcrumbLink,-->
<!--		BreadcrumbList,-->
<!--		BreadcrumbPage,-->
<!--		BreadcrumbSeparator,-->
<!--	} from '@/components/ui/breadcrumb';-->
<!--	import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';-->
<!--	import DynamicForm from '@/components/modules/DynamicForm.svelte';-->
<!--	import type { Module } from '@/types/modules';-->

<!--	interface Props {-->
<!--		module: Module;-->
<!--		errors?: Record<string, string>;-->
<!--	}-->

<!--	let { module, errors = {} }: Props = $props();-->

<!--	let isSubmitting = $state(false);-->

<!--	function handleSubmit(data: Record<string, any>) {-->
<!--		isSubmitting = true;-->

<!--		router.post(`/api/modules/${module.api_name}/records`, data, {-->
<!--			onSuccess: (page) => {-->
<!--				toast.success(`${module.name.replace(/s$/, '')} created successfully`);-->
<!--				// Extract the created record ID from response-->
<!--				const recordId = (page.props as any).record?.id;-->
<!--				if (recordId) {-->
<!--					router.visit(`/modules/${module.api_name}/${recordId}`);-->
<!--				} else {-->
<!--					router.visit(`/modules/${module.api_name}`);-->
<!--				}-->
<!--			},-->
<!--			onError: (errors) => {-->
<!--				toast.error('Failed to create record. Please check the form for errors.');-->
<!--				isSubmitting = false;-->
<!--			},-->
<!--			onFinish: () => {-->
<!--				isSubmitting = false;-->
<!--			},-->
<!--		});-->
<!--	}-->

<!--	function handleCancel() {-->
<!--		router.visit(`/modules/${module.api_name}`);-->
<!--	}-->
<!--</script>-->

<!--<svelte:head>-->
<!--	<title>New {module.name.replace(/s$/, '')} | {module.name} | VrtxCRM</title>-->
<!--</svelte:head>-->

<!--<AppLayout>-->
<!--	<div class="flex flex-col gap-6">-->
<!--		&lt;!&ndash; Breadcrumb &ndash;&gt;-->
<!--		<Breadcrumb>-->
<!--			<BreadcrumbList>-->
<!--				<BreadcrumbItem>-->
<!--					<BreadcrumbLink href="/">Dashboard</BreadcrumbLink>-->
<!--				</BreadcrumbItem>-->
<!--				<BreadcrumbSeparator />-->
<!--				<BreadcrumbItem>-->
<!--					<BreadcrumbLink href={`/modules/${module.api_name}`}>-->
<!--						{module.name}-->
<!--					</BreadcrumbLink>-->
<!--				</BreadcrumbItem>-->
<!--				<BreadcrumbSeparator />-->
<!--				<BreadcrumbItem>-->
<!--					<BreadcrumbPage>New {module.name.replace(/s$/, '')}</BreadcrumbPage>-->
<!--				</BreadcrumbItem>-->
<!--			</BreadcrumbList>-->
<!--		</Breadcrumb>-->

<!--		&lt;!&ndash; Header &ndash;&gt;-->
<!--		<div>-->
<!--			<h1 class="text-3xl font-bold tracking-tight">-->
<!--				New {module.name.replace(/s$/, '')}-->
<!--			</h1>-->
<!--			<p class="text-muted-foreground">-->
<!--				Create a new {module.name.toLowerCase().replace(/s$/, '')} record-->
<!--			</p>-->
<!--		</div>-->

<!--		&lt;!&ndash; Form &ndash;&gt;-->
<!--		<DynamicForm-->
<!--			{module}-->
<!--			onSubmit={handleSubmit}-->
<!--			onCancel={handleCancel}-->
<!--			{isSubmitting}-->
<!--			{errors}-->
<!--		/>-->
<!--	</div>-->
<!--</AppLayout>-->
