<script lang="ts">
	import { router, useForm } from '@inertiajs/svelte';
	import { Button } from '@/components/ui/button';
	import { Input } from '@/components/ui/input';
	import { Label } from '@/components/ui/label';
	import { Textarea } from '@/components/ui/textarea';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
	import { Switch } from '@/components/ui/switch';
	import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
	import {
		ArrowLeft,
		Save,
		Loader2,
		Users,
		Building2,
		Briefcase,
		BarChart3,
		TrendingUp,
		DollarSign,
		Target,
		Calendar,
		Mail,
		Phone,
		Home,
		Rocket,
		Settings,
		Palette,
		Package,
		Wrench,
		Lightbulb,
		Bell,
		FileText,
		type Icon
	} from 'lucide-svelte';
	import { toast } from 'svelte-sonner';
	import type { ComponentType } from 'svelte';

	const form = useForm({
		name: '',
		singular_name: '',
		icon: '',
		description: '',
		is_active: true
	});

	// Auto-generate singular name from name
	$effect(() => {
		if (form.data.name && !form.data.singular_name) {
			// Remove trailing 's' if present
			form.data.singular_name = form.data.name.endsWith('s')
				? form.data.name.slice(0, -1)
				: form.data.name;
		}
	});

	function handleCancel() {
		router.visit('/admin/modules');
	}

	let saving = $state(false);

	async function handleSave() {
		saving = true;
		form.clearErrors();

		try {
			const response = await fetch('/api/admin/modules', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
					'Accept': 'application/json'
				},
				body: JSON.stringify(form.data)
			});

			const data = await response.json();

			if (!response.ok) {
				if (data.errors) {
					// Set form errors
					Object.keys(data.errors).forEach(key => {
						form.setError(key, data.errors[key][0] || data.errors[key]);
					});
					toast.error('Validation failed. Please check the form.');
				} else {
					toast.error(data.error || 'Failed to create module');
				}
				return;
			}

			toast.success('Module created successfully!');
			router.visit(`/admin/modules/${data.module.id}/edit`);
		} catch (error) {
			console.error('Failed to create module:', error);
			toast.error('An error occurred. Please try again.');
		} finally {
			saving = false;
		}
	}

	// Common icons for modules
	const commonIcons: Array<{ name: string; component: ComponentType }> = [
		{ name: 'FileText', component: FileText },
		{ name: 'Users', component: Users },
		{ name: 'Building2', component: Building2 },
		{ name: 'Briefcase', component: Briefcase },
		{ name: 'BarChart3', component: BarChart3 },
		{ name: 'TrendingUp', component: TrendingUp },
		{ name: 'DollarSign', component: DollarSign },
		{ name: 'Target', component: Target },
		{ name: 'Calendar', component: Calendar },
		{ name: 'Mail', component: Mail },
		{ name: 'Phone', component: Phone },
		{ name: 'Home', component: Home },
		{ name: 'Rocket', component: Rocket },
		{ name: 'Settings', component: Settings },
		{ name: 'Palette', component: Palette },
		{ name: 'Package', component: Package },
		{ name: 'Wrench', component: Wrench },
		{ name: 'Lightbulb', component: Lightbulb },
		{ name: 'Bell', component: Bell }
	];
</script>

<svelte:head>
	<title>Create Module | VrtxCRM</title>
</svelte:head>

<AppLayout>
	<div class="flex flex-col gap-6 max-w-4xl">
		<!-- Header -->
		<div class="flex items-center gap-4">
			<Button variant="outline" size="icon" onclick={handleCancel}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div>
				<h1 class="text-3xl font-bold tracking-tight">Create Module</h1>
				<p class="text-muted-foreground">
					Define a new custom module for your CRM
				</p>
			</div>
		</div>

		<form onsubmit={(e) => { e.preventDefault(); handleSave(); }}>
			<div class="space-y-6">
				<!-- Basic Information -->
				<Card>
					<CardHeader>
						<CardTitle>Basic Information</CardTitle>
						<CardDescription>
							Provide the basic details for your module
						</CardDescription>
					</CardHeader>
					<CardContent class="space-y-4">
						<!-- Module Name -->
						<div class="space-y-2">
							<Label for="name">Module Name *</Label>
							<Input
								id="name"
								bind:value={form.data.name}
								placeholder="e.g., Projects, Leads, Invoices"
								required
								autofocus
							/>
							{#if form.errors.name}
								<p class="text-sm text-destructive">{form.errors.name}</p>
							{/if}
							<p class="text-xs text-muted-foreground">
								The plural name displayed in navigation and lists
							</p>
						</div>

						<!-- Singular Name -->
						<div class="space-y-2">
							<Label for="singular_name">Singular Name *</Label>
							<Input
								id="singular_name"
								bind:value={form.data.singular_name}
								placeholder="e.g., Project, Lead, Invoice"
								required
							/>
							{#if form.errors.singular_name}
								<p class="text-sm text-destructive">{form.errors.singular_name}</p>
							{/if}
							<p class="text-xs text-muted-foreground">
								Used for single records (e.g., "Create Project")
							</p>
						</div>

						<!-- Icon -->
						<div class="space-y-2">
							<Label for="icon">Icon</Label>
							<div class="flex flex-wrap gap-2">
								{#each commonIcons as iconOption}
									{#snippet iconBtn()}
										{@const IconComp = iconOption.component}
										<button
											type="button"
											onclick={() => (form.data.icon = iconOption.name)}
											class="h-10 w-10 rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex items-center justify-center transition-colors"
											class:bg-accent={form.data.icon === iconOption.name}
											class:text-accent-foreground={form.data.icon === iconOption.name}
											title={iconOption.name}
										>
											<IconComp class="h-5 w-5" />
										</button>
									{/snippet}
									{@render iconBtn()}
								{/each}
							</div>
							<p class="text-xs text-muted-foreground">
								Select an icon to represent this module
							</p>
						</div>

						<!-- Description -->
						<div class="space-y-2">
							<Label for="description">Description</Label>
							<Textarea
								id="description"
								bind:value={form.data.description}
								placeholder="What is this module used for?"
								rows={3}
							/>
							<p class="text-xs text-muted-foreground">
								Help users understand the purpose of this module
							</p>
						</div>

						<!-- Active Status -->
						<div class="flex items-center justify-between space-x-2 rounded-lg border p-4">
							<div class="space-y-0.5">
								<Label for="is_active">Active</Label>
								<p class="text-sm text-muted-foreground">
									Inactive modules are hidden from users
								</p>
							</div>
							<Switch id="is_active" bind:checked={form.data.is_active} />
						</div>
					</CardContent>
				</Card>

				<!-- Actions -->
				<div class="flex items-center gap-4">
					<Button type="submit" disabled={saving}>
						{#if saving}
							<Loader2 class="mr-2 h-4 w-4 animate-spin" />
							Creating...
						{:else}
							<Save class="mr-2 h-4 w-4" />
							Create Module
						{/if}
					</Button>
					<Button type="button" variant="outline" onclick={handleCancel} disabled={saving}>
						Cancel
					</Button>
				</div>

				<!-- Help Text -->
				<Card class="border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950">
					<CardContent class="pt-6">
						<p class="text-sm text-blue-900 dark:text-blue-100">
							<strong>Next steps:</strong> After creating the module, you'll be able to add fields, organize them into blocks, and configure relationships with other modules.
						</p>
					</CardContent>
				</Card>
			</div>
		</form>
	</div>
</AppLayout>
