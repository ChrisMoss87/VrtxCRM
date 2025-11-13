<script lang="ts">
	import { router } from '@inertiajs/svelte';
	import { Button } from '@/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
	import { Badge } from '@/components/ui/badge';
	import * as DropdownMenu from '@/components/ui/dropdown-menu';
	import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
	import { Plus, MoreVertical, Edit, Trash2, Database, Grid3x3, Power, PowerOff } from 'lucide-svelte';

	interface Module {
		id: number;
		name: string;
		singular_name: string;
		api_name: string;
		icon: string | null;
		description: string | null;
		is_active: boolean;
		is_system: boolean;
		blocks_count?: number;
		fields_count?: number;
		records_count?: number;
		created_at: string;
	}

	interface Props {
		modules: Module[];
	}

	let { modules }: Props = $props();

	function handleCreateNew() {
		router.visit('/admin/modules/create');
	}

	function handleEdit(moduleId: number) {
		router.visit(`/admin/modules/${moduleId}/edit`);
	}

	function handleView(module: Module) {
		router.visit(`/modules/${module.api_name}`);
	}

	async function handleToggleActive(module: Module) {
		if (module.is_system) {
			alert('Cannot modify system modules');
			return;
		}

		const endpoint = module.is_active
			? `/api/admin/modules/${module.id}/deactivate`
			: `/api/admin/modules/${module.id}/activate`;

		try {
			const response = await fetch(endpoint, {
				method: 'PATCH',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
				}
			});

			if (response.ok) {
				router.reload();
			}
		} catch (error) {
			console.error('Failed to toggle module status:', error);
		}
	}

	async function handleDelete(module: Module) {
		if (module.is_system) {
			alert('Cannot delete system modules');
			return;
		}

		if (!confirm(`Are you sure you want to delete "${module.name}"? This action cannot be undone.`)) {
			return;
		}

		try {
			const response = await fetch(`/api/admin/modules/${module.id}`, {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
				}
			});

			if (response.ok) {
				router.reload();
			}
		} catch (error) {
			console.error('Failed to delete module:', error);
		}
	}
</script>

<svelte:head>
	<title>Module Builder | VrtxCRM</title>
</svelte:head>

<AppLayout>
	<div class="flex flex-col gap-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-3xl font-bold tracking-tight">Module Builder</h1>
				<p class="text-muted-foreground">
					Create and manage custom modules for your CRM
				</p>
			</div>

			<Button onclick={handleCreateNew}>
				<Plus class="mr-2 h-4 w-4" />
				Create Module
			</Button>
		</div>

		<!-- Modules Grid -->
		{#if modules.length === 0}
			<Card>
				<CardContent class="flex flex-col items-center justify-center py-12">
					<Database class="h-12 w-12 text-muted-foreground mb-4" />
					<h3 class="text-lg font-semibold mb-2">No modules yet</h3>
					<p class="text-muted-foreground text-center mb-4">
						Get started by creating your first custom module
					</p>
					<Button onclick={handleCreateNew}>
						<Plus class="mr-2 h-4 w-4" />
						Create Module
					</Button>
				</CardContent>
			</Card>
		{:else}
			<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
				{#each modules as module (module.id)}
					<Card class="relative hover:shadow-md transition-shadow">
						<CardHeader>
							<div class="flex items-start justify-between">
								<div class="flex items-center gap-3">
									{#if module.icon}
										<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
											<span class="text-xl">{module.icon}</span>
										</div>
									{:else}
										<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
											<Database class="h-5 w-5 text-muted-foreground" />
										</div>
									{/if}
									<div>
										<CardTitle class="text-lg">{module.name}</CardTitle>
										<CardDescription class="text-xs">{module.api_name}</CardDescription>
									</div>
								</div>

								<DropdownMenu.Root>
									<DropdownMenu.Trigger asChild>
										{#snippet child({ props })}
											<Button {...props} variant="ghost" size="icon" class="h-8 w-8">
												<MoreVertical class="h-4 w-4" />
											</Button>
										{/snippet}
									</DropdownMenu.Trigger>
									<DropdownMenu.Content align="end">
										<DropdownMenu.Item onclick={() => handleView(module)}>
											<Grid3x3 class="mr-2 h-4 w-4" />
											View Records
										</DropdownMenu.Item>
										<DropdownMenu.Item onclick={() => handleEdit(module.id)}>
											<Edit class="mr-2 h-4 w-4" />
											Edit Module
										</DropdownMenu.Item>
										{#if !module.is_system}
											<DropdownMenu.Separator />
											<DropdownMenu.Item onclick={() => handleToggleActive(module)}>
												{#if module.is_active}
													<PowerOff class="mr-2 h-4 w-4" />
													Deactivate
												{:else}
													<Power class="mr-2 h-4 w-4" />
													Activate
												{/if}
											</DropdownMenu.Item>
											<DropdownMenu.Separator />
											<DropdownMenu.Item
												class="text-destructive"
												onclick={() => handleDelete(module)}
											>
												<Trash2 class="mr-2 h-4 w-4" />
												Delete
											</DropdownMenu.Item>
										{/if}
									</DropdownMenu.Content>
								</DropdownMenu.Root>
							</div>
						</CardHeader>
						<CardContent>
							<div class="space-y-3">
								{#if module.description}
									<p class="text-sm text-muted-foreground line-clamp-2">
										{module.description}
									</p>
								{/if}

								<div class="flex items-center gap-2 flex-wrap">
									{#if module.is_system}
										<Badge variant="secondary">System</Badge>
									{/if}
									{#if module.is_active}
										<Badge variant="default">Active</Badge>
									{:else}
										<Badge variant="outline">Inactive</Badge>
									{/if}
								</div>

								<div class="grid grid-cols-3 gap-2 pt-2 border-t">
									<div class="text-center">
										<div class="text-2xl font-bold">{module.fields_count ?? 0}</div>
										<div class="text-xs text-muted-foreground">Fields</div>
									</div>
									<div class="text-center">
										<div class="text-2xl font-bold">{module.blocks_count ?? 0}</div>
										<div class="text-xs text-muted-foreground">Blocks</div>
									</div>
									<div class="text-center">
										<div class="text-2xl font-bold">{module.records_count ?? 0}</div>
										<div class="text-xs text-muted-foreground">Records</div>
									</div>
								</div>
							</div>
						</CardContent>
					</Card>
				{/each}
			</div>
		{/if}
	</div>
</AppLayout>
