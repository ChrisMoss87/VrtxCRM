<script lang="ts">
	import { router, useForm } from '@inertiajs/svelte';
	import { Button } from '@/components/ui/button';
	import { Input } from '@/components/ui/input';
	import { Label } from '@/components/ui/label';
	import { Textarea } from '@/components/ui/textarea';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
	import { Switch } from '@/components/ui/switch';
	import { Tabs } from '@/components/ui/tabs';
	import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
	import {
		ArrowLeft,
		Save,
		Loader2,
		Plus,
		Trash2,
		GripVertical,
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
		FileText
	} from 'lucide-svelte';
	import { toast } from 'svelte-sonner';
	import type { ComponentType } from 'svelte';
	import { dndzone } from 'svelte-dnd-action';
	import type { DndEvent } from 'svelte-dnd-action';

	interface FieldOption {
		id?: number;
		label: string;
		value: string;
		color: string | null;
		order: number;
		is_default: boolean;
	}

	interface Field {
		id?: number;
		type: string;
		api_name: string;
		label: string;
		description: string | null;
		help_text: string | null;
		is_required: boolean;
		is_unique: boolean;
		is_searchable: boolean;
		order: number;
		default_value: string | null;
		validation_rules: Record<string, any>;
		settings: Record<string, any>;
		width: number;
		options: FieldOption[];
	}

	interface Block {
		id?: number;
		type: string;
		label: string;
		order: number;
		settings: Record<string, any>;
		fields: Field[];
	}

	interface Module {
		id: number;
		name: string;
		singular_name: string;
		api_name: string;
		icon: string | null;
		description: string | null;
		is_active: boolean;
		is_system: boolean;
		settings: Record<string, any>;
		blocks: Block[];
	}

	interface Props {
		module: Module;
		fieldTypes: string[];
	}

	let { module, fieldTypes }: Props = $props();

	// Basic Info Form
	const basicForm = useForm({
		name: module.name,
		singular_name: module.singular_name,
		icon: module.icon || '',
		description: module.description || '',
		is_active: module.is_active
	});

	// Fields & Blocks state (simple reactivity)
	let blocks = $state<Block[]>(JSON.parse(JSON.stringify(module.blocks)));
	let savingStructure = $state(false);
	let activeTab = $state('basic');

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

	// Helper to get icon component by name
	function getIconComponent(iconName: string | null): ComponentType | null {
		if (!iconName) return null;
		const iconOption = commonIcons.find((i) => i.name === iconName);
		return iconOption?.component || null;
	}

	function handleCancel() {
		router.visit('/admin/modules');
	}

	async function handleSaveBasic() {
		try {
			const response = await fetch(`/api/admin/modules/${module.id}`, {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN':
						document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
				},
				body: JSON.stringify(basicForm.data)
			});

			const data = await response.json();

			if (!response.ok) {
				if (data.errors) {
					Object.keys(data.errors).forEach((key) => {
						basicForm.setError(key, data.errors[key][0] || data.errors[key]);
					});
					toast.error('Validation failed. Please check the form.');
				} else {
					toast.error(data.error || 'Failed to update module');
				}
				return;
			}

			toast.success('Module updated successfully!');
			router.reload({ only: ['module'] });
		} catch (error) {
			console.error('Failed to update module:', error);
			toast.error('An error occurred. Please try again.');
		}
	}

	function addBlock() {
		blocks = [
			...blocks,
			{
				type: 'section',
				label: 'New Section',
				order: blocks.length,
				settings: {},
				fields: []
			}
		];
	}

	function removeBlock(index: number) {
		if (confirm('Are you sure you want to delete this block and all its fields?')) {
			blocks = blocks.filter((_, i) => i !== index);
		}
	}

	function addField(blockIndex: number) {
		const block = blocks[blockIndex];
		block.fields = [
			...block.fields,
			{
				type: 'text',
				api_name: '',
				label: '',
				description: null,
				help_text: null,
				is_required: false,
				is_unique: false,
				is_searchable: false,
				order: block.fields.length,
				default_value: null,
				validation_rules: {},
				settings: {},
				width: 100,
				options: []
			}
		];
		blocks = [...blocks]; // Trigger reactivity
	}

	function removeField(blockIndex: number, fieldIndex: number) {
		if (confirm('Are you sure you want to delete this field?')) {
			blocks[blockIndex].fields = blocks[blockIndex].fields.filter((_, i) => i !== fieldIndex);
			blocks = [...blocks]; // Trigger reactivity
		}
	}

	function generateApiName(label: string): string {
		return label
			.toLowerCase()
			.replace(/\s+/g, '_')
			.replace(/[^a-z0-9_]/g, '');
	}

	// Drag and drop handlers for blocks
	function handleBlocksSort(e: CustomEvent<DndEvent<Block>>) {
		blocks = e.detail.items;
		// Update order property
		blocks.forEach((block, index) => {
			block.order = index;
		});
	}

	// Drag and drop handlers for fields
	function handleFieldsSort(blockIndex: number, e: CustomEvent<DndEvent<Field>>) {
		blocks[blockIndex].fields = e.detail.items;
		// Update order property
		blocks[blockIndex].fields.forEach((field, index) => {
			field.order = index;
		});
		blocks = [...blocks]; // Trigger reactivity
	}

	async function handleSaveStructure() {
		savingStructure = true;

		try {
			const response = await fetch(`/api/admin/modules/${module.id}/sync-structure`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN':
						document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
				},
				body: JSON.stringify({ blocks })
			});

			const data = await response.json();

			if (!response.ok) {
				toast.error(data.error || 'Failed to save structure');
				return;
			}

			toast.success('Fields and blocks saved successfully!');
			router.reload({ only: ['module'] });
		} catch (error) {
			console.error('Failed to save structure:', error);
			toast.error('An error occurred. Please try again.');
		} finally {
			savingStructure = false;
		}
	}
</script>

<svelte:head>
	<title>Edit Module: {module.name} | VrtxCRM</title>
</svelte:head>

<AppLayout>
	<div class="flex flex-col gap-6 max-w-6xl">
		<!-- Header -->
		<div class="flex items-center justify-between gap-4">
			<div class="flex items-center gap-4">
				<Button variant="outline" size="icon" onclick={handleCancel}>
					<ArrowLeft class="h-4 w-4" />
				</Button>
				<div>
					<h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
						{#if module.icon}
							{#snippet moduleIcon()}
								{@const IconComponent = getIconComponent(module.icon)}
								{#if IconComponent}
									<IconComponent class="h-8 w-8" />
								{/if}
							{/snippet}
							{@render moduleIcon()}
						{/if}
						Edit Module: {module.name}
					</h1>
					<p class="text-muted-foreground">Configure module settings, fields, and blocks</p>
				</div>
			</div>
			{#if module.is_system}
				<div
					class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100"
				>
					System Module
				</div>
			{/if}
		</div>

		<!-- Tabs -->
		<Tabs.Root bind:value={activeTab}>
			<Tabs.List class="grid w-full grid-cols-3">
				<Tabs.Trigger value="basic">Basic Info</Tabs.Trigger>
				<Tabs.Trigger value="fields">Fields & Blocks</Tabs.Trigger>
				<Tabs.Trigger value="settings">Settings</Tabs.Trigger>
			</Tabs.List>

			<!-- Basic Info Tab -->
			<Tabs.Content value="basic">
				<form
					onsubmit={(e) => {
						e.preventDefault();
						handleSaveBasic();
					}}
				>
					<div class="space-y-6 py-6">
						<Card>
							<CardHeader>
								<CardTitle>Basic Information</CardTitle>
								<CardDescription>Update the basic details for your module</CardDescription>
							</CardHeader>
							<CardContent class="space-y-4">
								<!-- Module Name -->
								<div class="space-y-2">
									<Label for="name">Module Name *</Label>
									<Input
										id="name"
										bind:value={basicForm.data.name}
										placeholder="e.g., Projects, Leads, Invoices"
										required
										disabled={module.is_system}
									/>
									{#if basicForm.errors.name}
										<p class="text-sm text-destructive">{basicForm.errors.name}</p>
									{/if}
								</div>

								<!-- Singular Name -->
								<div class="space-y-2">
									<Label for="singular_name">Singular Name *</Label>
									<Input
										id="singular_name"
										bind:value={basicForm.data.singular_name}
										placeholder="e.g., Project, Lead, Invoice"
										required
										disabled={module.is_system}
									/>
									{#if basicForm.errors.singular_name}
										<p class="text-sm text-destructive">{basicForm.errors.singular_name}</p>
									{/if}
								</div>

								<!-- API Name (Read-only) -->
								<div class="space-y-2">
									<Label for="api_name">API Name</Label>
									<Input id="api_name" value={module.api_name} disabled />
									<p class="text-xs text-muted-foreground">
										API name cannot be changed after creation
									</p>
								</div>

								<!-- Icon -->
								<div class="space-y-2">
									<Label>Icon</Label>
									<div class="flex flex-wrap gap-2">
										{#each commonIcons as iconOption}
											{#snippet iconBtn()}
												{@const IconComp = iconOption.component}
												<button
													type="button"
													onclick={() => (basicForm.data.icon = iconOption.name)}
													class="h-10 w-10 rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex items-center justify-center transition-colors"
													class:bg-accent={basicForm.data.icon === iconOption.name}
													class:text-accent-foreground={basicForm.data.icon === iconOption.name}
													title={iconOption.name}
												>
													<IconComp class="h-5 w-5" />
												</button>
											{/snippet}
											{@render iconBtn()}
										{/each}
									</div>
								</div>

								<!-- Description -->
								<div class="space-y-2">
									<Label for="description">Description</Label>
									<Textarea
										id="description"
										bind:value={basicForm.data.description}
										placeholder="What is this module used for?"
										rows={3}
									/>
								</div>

								<!-- Active Status -->
								<div
									class="flex items-center justify-between space-x-2 rounded-lg border p-4"
								>
									<div class="space-y-0.5">
										<Label for="is_active">Active</Label>
										<p class="text-sm text-muted-foreground">
											Inactive modules are hidden from users
										</p>
									</div>
									<Switch id="is_active" bind:checked={basicForm.data.is_active} />
								</div>
							</CardContent>
						</Card>

						<!-- Actions -->
						<div class="flex items-center gap-4">
							<Button type="submit" disabled={basicForm.processing || module.is_system}>
								{#if basicForm.processing}
									<Loader2 class="mr-2 h-4 w-4 animate-spin" />
									Saving...
								{:else}
									<Save class="mr-2 h-4 w-4" />
									Save Changes
								{/if}
							</Button>
							<Button
								type="button"
								variant="outline"
								onclick={handleCancel}
								disabled={basicForm.processing}
							>
								Cancel
							</Button>
						</div>
					</div>
				</form>
			</Tabs.Content>

			<!-- Fields & Blocks Tab -->
			<Tabs.Content value="fields">
				<div class="space-y-6 py-6">
					<!-- Blocks List -->
					{#if blocks.length === 0}
						<Card class="border-dashed">
							<CardContent
								class="flex flex-col items-center justify-center py-12 text-center"
							>
								<p class="text-sm text-muted-foreground mb-4">
									No blocks defined yet. Add a block to start organizing your fields.
								</p>
								<Button onclick={addBlock} disabled={module.is_system}>
									<Plus class="mr-2 h-4 w-4" />
									Add First Block
								</Button>
							</CardContent>
						</Card>
					{:else}
						<div
							class="space-y-4"
							use:dndzone={{
								items: blocks,
								dragDisabled: module.is_system,
								dropTargetStyle: {},
								type: 'blocks'
							}}
							onconsider={handleBlocksSort}
							onfinalize={handleBlocksSort}
						>
							{#each blocks as block, blockIndex (block.id || blockIndex)}
								<Card data-testid="block-item">
									<CardHeader>
										<div class="flex items-center justify-between">
											<div class="flex items-center gap-3 flex-1">
												<GripVertical
													class="h-5 w-5 text-muted-foreground cursor-move"
												/>
												<div class="flex-1">
													<Input
														bind:value={block.label}
														class="font-semibold text-lg h-auto border-0 px-0 focus-visible:ring-0"
														placeholder="Block Label"
														disabled={module.is_system}
													/>
													<p class="text-xs text-muted-foreground mt-1">
														{block.fields.length} field{block.fields.length !== 1
															? 's'
															: ''}
													</p>
												</div>
											</div>
											<div class="flex items-center gap-2">
												<Button
													variant="outline"
													size="sm"
													onclick={() => addField(blockIndex)}
													disabled={module.is_system}
													data-testid="add-field-button"
												>
													<Plus class="h-4 w-4 mr-1" />
													Add Field
												</Button>
												<Button
													variant="ghost"
													size="icon"
													onclick={() => removeBlock(blockIndex)}
													disabled={module.is_system}
													data-testid="delete-block-button"
												>
													<Trash2 class="h-4 w-4 text-destructive" />
												</Button>
											</div>
										</div>
									</CardHeader>

									{#if block.fields.length > 0}
										<CardContent>
											<div
												class="space-y-3"
												use:dndzone={{
													items: block.fields,
													dragDisabled: module.is_system,
													dropTargetStyle: {},
													type: `fields-${blockIndex}`
												}}
												onconsider={(e) => handleFieldsSort(blockIndex, e)}
												onfinalize={(e) => handleFieldsSort(blockIndex, e)}
											>
												{#each block.fields as field, fieldIndex (field.id || fieldIndex)}
													<div class="flex items-start gap-3 p-4 border rounded-lg" data-testid="field-item">
														<GripVertical
															class="h-5 w-5 text-muted-foreground cursor-move mt-2"
														/>
														<div class="flex-1 grid grid-cols-2 gap-4">
															<!-- Field Label -->
															<div class="space-y-1.5">
																<Label>Label *</Label>
																<Input
																	bind:value={field.label}
																	placeholder="Field Label"
																	disabled={module.is_system}
																	oninput={() => {
																		if (!field.api_name && field.label) {
																			field.api_name = generateApiName(field.label);
																		}
																	}}
																/>
															</div>

															<!-- Field API Name -->
															<div class="space-y-1.5">
																<Label>API Name *</Label>
																<Input
																	bind:value={field.api_name}
																	placeholder="api_name"
																	pattern="[a-z][a-z0-9_]*"
																	disabled={module.is_system}
																/>
															</div>

															<!-- Field Type -->
															<div class="space-y-1.5">
																<Label>Type *</Label>
																<select
																	bind:value={field.type}
																	class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
																	disabled={module.is_system}
																>
																	{#each fieldTypes as fieldType}
																		<option value={fieldType}>{fieldType}</option>
																	{/each}
																</select>
															</div>

															<!-- Field Width -->
															<div class="space-y-1.5">
																<Label>Width (%)</Label>
																<select
																	bind:value={field.width}
																	class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
																	disabled={module.is_system}
																>
																	<option value={25}>25%</option>
																	<option value={33}>33%</option>
																	<option value={50}>50%</option>
																	<option value={66}>66%</option>
																	<option value={75}>75%</option>
																	<option value={100}>100%</option>
																</select>
															</div>

															<!-- Field Description (full width) -->
															<div class="space-y-1.5 col-span-2">
																<Label>Description</Label>
																<Input
																	bind:value={field.description}
																	placeholder="Optional field description"
																	disabled={module.is_system}
																/>
															</div>

															<!-- Field Flags -->
															<div class="col-span-2 flex items-center gap-6">
																<label class="flex items-center gap-2 text-sm">
																	<input
																		type="checkbox"
																		bind:checked={field.is_required}
																		disabled={module.is_system}
																		class="rounded"
																	/>
																	Required
																</label>
																<label class="flex items-center gap-2 text-sm">
																	<input
																		type="checkbox"
																		bind:checked={field.is_unique}
																		disabled={module.is_system}
																		class="rounded"
																	/>
																	Unique
																</label>
																<label class="flex items-center gap-2 text-sm">
																	<input
																		type="checkbox"
																		bind:checked={field.is_searchable}
																		disabled={module.is_system}
																		class="rounded"
																	/>
																	Searchable
																</label>
															</div>

															<!-- Field Type-Specific Settings -->
															{#if ['select', 'radio', 'multiselect'].includes(field.type)}
																<!-- Options for select/radio/multiselect -->
																<div class="col-span-2 space-y-2">
																	<Label>Options</Label>
																	<div class="space-y-2">
																		{#if !field.options || field.options.length === 0}
																			<p class="text-sm text-muted-foreground">No options defined</p>
																		{:else}
																			{#each field.options as option, optIndex}
																				<div class="flex items-center gap-2">
																					<Input
																						bind:value={option.label}
																						placeholder="Label"
																						disabled={module.is_system}
																						class="flex-1"
																					/>
																					<Input
																						bind:value={option.value}
																						placeholder="Value"
																						disabled={module.is_system}
																						class="flex-1"
																					/>
																					<Button
																						variant="ghost"
																						size="icon"
																						onclick={() => {
																							field.options = field.options.filter((_, i) => i !== optIndex);
																							blocks = [...blocks];
																						}}
																						disabled={module.is_system}
																					>
																						<Trash2 class="h-4 w-4" />
																					</Button>
																				</div>
																			{/each}
																		{/if}
																		<Button
																			variant="outline"
																			size="sm"
																			onclick={() => {
																				if (!field.options) field.options = [];
																				field.options = [
																					...field.options,
																					{
																						label: '',
																						value: '',
																						color: null,
																						order: field.options.length,
																						is_default: false
																					}
																				];
																				blocks = [...blocks];
																			}}
																			disabled={module.is_system}
																		>
																			<Plus class="h-4 w-4 mr-1" />
																			Add Option
																		</Button>
																	</div>
																</div>
															{/if}

															{#if ['number', 'decimal', 'currency', 'percent'].includes(field.type)}
																<!-- Number constraints -->
																<div class="space-y-1.5">
																	<Label>Minimum Value</Label>
																	<Input
																		type="number"
																		bind:value={field.settings.min}
																		placeholder="No minimum"
																		disabled={module.is_system}
																	/>
																</div>
																<div class="space-y-1.5">
																	<Label>Maximum Value</Label>
																	<Input
																		type="number"
																		bind:value={field.settings.max}
																		placeholder="No maximum"
																		disabled={module.is_system}
																	/>
																</div>
															{/if}

															{#if field.type === 'decimal'}
																<div class="space-y-1.5 col-span-2">
																	<Label>Decimal Places</Label>
																	<Input
																		type="number"
																		bind:value={field.settings.decimal_places}
																		placeholder="2"
																		min="0"
																		max="10"
																		disabled={module.is_system}
																	/>
																</div>
															{/if}

															{#if ['text', 'textarea'].includes(field.type)}
																<!-- Text constraints -->
																<div class="space-y-1.5">
																	<Label>Min Length</Label>
																	<Input
																		type="number"
																		bind:value={field.settings.min_length}
																		placeholder="No minimum"
																		disabled={module.is_system}
																	/>
																</div>
																<div class="space-y-1.5">
																	<Label>Max Length</Label>
																	<Input
																		type="number"
																		bind:value={field.settings.max_length}
																		placeholder="No maximum"
																		disabled={module.is_system}
																	/>
																</div>
															{/if}

															{#if field.type === 'textarea'}
																<div class="space-y-1.5 col-span-2">
																	<Label>Rows</Label>
																	<Input
																		type="number"
																		bind:value={field.settings.rows}
																		placeholder="3"
																		min="1"
																		max="20"
																		disabled={module.is_system}
																	/>
																</div>
															{/if}

															{#if ['date', 'datetime'].includes(field.type)}
																<!-- Date constraints -->
																<div class="space-y-1.5">
																	<Label>Min Date</Label>
																	<Input
																		type="date"
																		bind:value={field.settings.min_date}
																		disabled={module.is_system}
																	/>
																</div>
																<div class="space-y-1.5">
																	<Label>Max Date</Label>
																	<Input
																		type="date"
																		bind:value={field.settings.max_date}
																		disabled={module.is_system}
																	/>
																</div>
															{/if}

															<!-- Default Value (for all types) -->
															<div class="space-y-1.5 col-span-2">
																<Label>Default Value</Label>
																<Input
																	bind:value={field.default_value}
																	placeholder="Optional default value"
																	disabled={module.is_system}
																/>
															</div>

															<!-- Help Text (for all types) -->
															<div class="space-y-1.5 col-span-2">
																<Label>Help Text</Label>
																<Input
																	bind:value={field.help_text}
																	placeholder="Optional help text shown below field"
																	disabled={module.is_system}
																/>
															</div>
														</div>

														<!-- Delete Field -->
														<Button
															variant="ghost"
															size="icon"
															onclick={() => removeField(blockIndex, fieldIndex)}
															disabled={module.is_system}
															data-testid="delete-field-button"
														>
															<Trash2 class="h-4 w-4 text-destructive" />
														</Button>
													</div>
												{/each}
											</div>
										</CardContent>
									{/if}
								</Card>
							{/each}
						</div>

						<!-- Add Block Button -->
						<Button
							variant="outline"
							onclick={addBlock}
							disabled={module.is_system}
							class="w-full"
							data-testid="add-block-button"
						>
							<Plus class="mr-2 h-4 w-4" />
							Add Block
						</Button>
					{/if}

					<!-- Save Button for Fields -->
					{#if blocks.length > 0}
						<div class="flex items-center gap-4">
							<Button
								onclick={handleSaveStructure}
								disabled={savingStructure || module.is_system}
							>
								{#if savingStructure}
									<Loader2 class="mr-2 h-4 w-4 animate-spin" />
									Saving...
								{:else}
									<Save class="mr-2 h-4 w-4" />
									Save Fields & Blocks
								{/if}
							</Button>
							<p class="text-sm text-muted-foreground">
								Save will persist all field and block changes
							</p>
						</div>
					{/if}
				</div>
			</Tabs.Content>

			<!-- Settings Tab -->
			<Tabs.Content value="settings">
				<div class="space-y-6 py-6">
					<Card>
						<CardHeader>
							<CardTitle>Module Settings</CardTitle>
							<CardDescription>Advanced configuration options</CardDescription>
						</CardHeader>
						<CardContent>
							<p class="text-sm text-muted-foreground">Additional settings coming soon...</p>
						</CardContent>
					</Card>
				</div>
			</Tabs.Content>
		</Tabs.Root>
	</div>
</AppLayout>
