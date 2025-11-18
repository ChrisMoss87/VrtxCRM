<!--<script lang="ts">-->
<!--	import { router } from '@inertiajs/svelte';-->
<!--	import { toast } from 'svelte-sonner';-->
<!--	import { Button } from '@/components/ui/button';-->
<!--	import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';-->
<!--	import * as AlertDialog from '@/components/ui/alert-dialog';-->
<!--	import {-->
<!--		Breadcrumb,-->
<!--		BreadcrumbItem,-->
<!--		BreadcrumbLink,-->
<!--		BreadcrumbList,-->
<!--		BreadcrumbPage,-->
<!--		BreadcrumbSeparator,-->
<!--	} from '@/components/ui/breadcrumb';-->
<!--	import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';-->
<!--	import FieldValue from '@/components/modules/FieldValue.svelte';-->
<!--	import type { Module, ModuleRecord } from '@/types/modules';-->
<!--	import { Edit, Trash2, ArrowLeft } from 'lucide-svelte';-->

<!--	interface Props {-->
<!--		module: Module;-->
<!--		record: ModuleRecord;-->
<!--	}-->

<!--	let { module, record }: Props = $props();-->

<!--	let showDeleteDialog = $state(false);-->
<!--	let isDeleting = $state(false);-->

<!--	function handleEdit() {-->
<!--		router.visit(`/modules/${module.api_name}/${record.id}/edit`);-->
<!--	}-->

<!--	function handleDelete() {-->
<!--		isDeleting = true;-->
<!--		router.delete(`/api/modules/${module.api_name}/records/${record.id}`, {-->
<!--			onSuccess: () => {-->
<!--				toast.success('Record deleted successfully');-->
<!--				router.visit(`/modules/${module.api_name}`);-->
<!--			},-->
<!--			onError: () => {-->
<!--				toast.error('Failed to delete record');-->
<!--				isDeleting = false;-->
<!--				showDeleteDialog = false;-->
<!--			},-->
<!--			onFinish: () => {-->
<!--				isDeleting = false;-->
<!--			},-->
<!--		});-->
<!--	}-->

<!--	function handleBack() {-->
<!--		router.visit(`/modules/${module.api_name}`);-->
<!--	}-->

<!--	// Get display name for record (first text field or ID)-->
<!--	const recordName = $derived(() => {-->
<!--		if (!module.blocks?.length) return `Record #${record.id}`;-->

<!--		const firstTextField = module.blocks[0]?.fields?.find(-->
<!--			(f) => f.type === 'text' && record.data[f.api_name]-->
<!--		);-->

<!--		return firstTextField-->
<!--			? String(record.data[firstTextField.api_name])-->
<!--			: `Record #${record.id}`;-->
<!--	});-->
<!--</script>-->

<!--<svelte:head>-->
<!--	<title>{recordName()} | {module.name} | VrtxCRM</title>-->
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
<!--					<BreadcrumbPage>{recordName()}</BreadcrumbPage>-->
<!--				</BreadcrumbItem>-->
<!--			</BreadcrumbList>-->
<!--		</Breadcrumb>-->

<!--		&lt;!&ndash; Header &ndash;&gt;-->
<!--		<div class="flex items-center justify-between">-->
<!--			<div class="flex items-center gap-4">-->
<!--				<Button variant="ghost" size="icon" onclick={handleBack}>-->
<!--					<ArrowLeft class="h-5 w-5" />-->
<!--				</Button>-->
<!--				<div>-->
<!--					<h1 class="text-3xl font-bold tracking-tight">{recordName()}</h1>-->
<!--					<p class="text-sm text-muted-foreground">-->
<!--						{module.name.replace(/s$/, '')} Details-->
<!--					</p>-->
<!--				</div>-->
<!--			</div>-->

<!--			<div class="flex items-center gap-2">-->
<!--				<Button variant="outline" onclick={handleEdit}>-->
<!--					<Edit class="mr-2 h-4 w-4" />-->
<!--					Edit-->
<!--				</Button>-->
<!--				<Button variant="destructive" onclick={() => (showDeleteDialog = true)}>-->
<!--					<Trash2 class="mr-2 h-4 w-4" />-->
<!--					Delete-->
<!--				</Button>-->
<!--			</div>-->
<!--		</div>-->

<!--		&lt;!&ndash; Blocks and Fields &ndash;&gt;-->
<!--		<div class="grid gap-6">-->
<!--			{#each module.blocks || [] as block (block.id)}-->
<!--				<Card>-->
<!--					<CardHeader>-->
<!--						<CardTitle>{block.name}</CardTitle>-->
<!--					</CardHeader>-->
<!--					<CardContent>-->
<!--						<div class="grid gap-6 md:grid-cols-2">-->
<!--							{#each block.fields as field (field.id)}-->
<!--								<div class="space-y-2">-->
<!--									<div class="text-sm font-medium text-muted-foreground">-->
<!--										{field.label}-->
<!--										{#if field.is_required}-->
<!--											<span class="text-destructive">*</span>-->
<!--										{/if}-->
<!--									</div>-->
<!--									<div class="text-base">-->
<!--										<FieldValue-->
<!--											value={record.data[field.api_name]}-->
<!--											{field}-->
<!--										/>-->
<!--									</div>-->
<!--									{#if field.help_text}-->
<!--										<p class="text-xs text-muted-foreground">-->
<!--											{field.help_text}-->
<!--										</p>-->
<!--									{/if}-->
<!--								</div>-->
<!--							{/each}-->
<!--						</div>-->
<!--					</CardContent>-->
<!--				</Card>-->
<!--			{/each}-->
<!--		</div>-->

<!--		&lt;!&ndash; Metadata &ndash;&gt;-->
<!--		<Card>-->
<!--			<CardHeader>-->
<!--				<CardTitle>Record Information</CardTitle>-->
<!--			</CardHeader>-->
<!--			<CardContent>-->
<!--				<div class="grid gap-4 md:grid-cols-3">-->
<!--					<div class="space-y-2">-->
<!--						<div class="text-sm font-medium text-muted-foreground">Record ID</div>-->
<!--						<div class="text-base">{record.id}</div>-->
<!--					</div>-->
<!--					<div class="space-y-2">-->
<!--						<div class="text-sm font-medium text-muted-foreground">Created</div>-->
<!--						<div class="text-base">-->
<!--							{new Date(record.created_at).toLocaleString()}-->
<!--						</div>-->
<!--					</div>-->
<!--					<div class="space-y-2">-->
<!--						<div class="text-sm font-medium text-muted-foreground">Last Updated</div>-->
<!--						<div class="text-base">-->
<!--							{new Date(record.updated_at).toLocaleString()}-->
<!--						</div>-->
<!--					</div>-->
<!--				</div>-->
<!--			</CardContent>-->
<!--		</Card>-->
<!--	</div>-->

<!--	&lt;!&ndash; Delete Confirmation Dialog &ndash;&gt;-->
<!--	<AlertDialog.Root bind:open={showDeleteDialog}>-->
<!--		<AlertDialog.Content>-->
<!--			<AlertDialog.Header>-->
<!--				<AlertDialog.Title>Are you sure?</AlertDialog.Title>-->
<!--				<AlertDialog.Description>-->
<!--					This action cannot be undone. This will permanently delete the {module.name.replace(-->
<!--						/s$/,-->
<!--						''-->
<!--					).toLowerCase()}.-->
<!--				</AlertDialog.Description>-->
<!--			</AlertDialog.Header>-->
<!--			<AlertDialog.Footer>-->
<!--				<AlertDialog.Cancel disabled={isDeleting}>Cancel</AlertDialog.Cancel>-->
<!--				<AlertDialog.Action-->
<!--					onclick={handleDelete}-->
<!--					disabled={isDeleting}-->
<!--					class="bg-destructive text-destructive-foreground hover:bg-destructive/90"-->
<!--				>-->
<!--					{isDeleting ? 'Deleting...' : 'Delete'}-->
<!--				</AlertDialog.Action>-->
<!--			</AlertDialog.Footer>-->
<!--		</AlertDialog.Content>-->
<!--	</AlertDialog.Root>-->
<!--</AppLayout>-->
