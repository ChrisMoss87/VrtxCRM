<script lang="ts">
	import { page } from '@inertiajs/svelte';
	import TextField from '@/components/form/TextField.svelte';
	import TextareaField from '@/components/form/TextareaField.svelte';
	import SelectField from '@/components/form/SelectField.svelte';
	import { Button } from '@/components/ui/button';

	interface FieldOption {
		id: number;
		field_id: number;
		label: string;
		value: string;
		order: number;
	}

	interface Field {
		id: number;
		module_id: number;
		block_id: number;
		name: string;
		api_name: string;
		type: string;
		description: string | null;
		is_required: boolean;
		is_unique: boolean;
		is_searchable: boolean;
		is_visible_in_list: boolean;
		is_visible_in_detail: boolean;
		is_editable: boolean;
		order: number;
		width: number;
		field_options?: FieldOption[];
	}

	interface Block {
		id: number;
		module_id: number;
		name: string;
		type: string;
		order: number;
		columns: number;
		is_collapsible: boolean;
		is_collapsed_by_default: boolean;
		fields: Field[];
	}

	interface Module {
		id: number;
		name: string;
		singular_name: string;
		icon: string;
		description: string;
		is_active: boolean;
		order: number;
		blocks: Block[];
	}

	const { module } = $page.props as { module: Module };

	let formData: Record<string, string> = $state({});

	function handleSubmit(e: Event) {
		e.preventDefault();
		console.log('Form data:', formData);
	}

	function renderField(field: Field) {
		const commonProps = {
			label: field.name,
			name: field.api_name,
			description: field.description || undefined,
			required: field.is_required,
			width: field.width as 25 | 50 | 75 | 100,
		};

		switch (field.type) {
			case 'text':
			case 'email':
				return {
					component: TextField,
					props: {
						...commonProps,
						type: field.type,
						bind: { value: formData[field.api_name] }
					}
				};

			case 'textarea':
				return {
					component: TextareaField,
					props: {
						...commonProps,
						bind: { value: formData[field.api_name] }
					}
				};

			case 'select':
				return {
					component: SelectField,
					props: {
						...commonProps,
						options: field.field_options?.map(opt => ({
							label: opt.label,
							value: opt.value
						})) || [],
						bind: { value: formData[field.api_name] }
					}
				};

			default:
				return null;
		}
	}
</script>

<div class="container mx-auto py-8">
	<div class="mb-8">
		<h1 class="text-3xl font-bold">{module.name}</h1>
		{#if module.description}
			<p class="text-muted-foreground mt-2">{module.description}</p>
		{/if}
	</div>

	<form onsubmit={handleSubmit} class="space-y-8">
		{#each module.blocks as block}
			<div class="rounded-lg border p-6">
				<h2 class="text-xl font-semibold mb-6">{block.name}</h2>

				<div class="grid gap-6" style="grid-template-columns: repeat({block.columns}, 1fr);">
					{#each block.fields as field}
						{@const fieldConfig = renderField(field)}
						{#if fieldConfig}
							{#if fieldConfig.component === TextField}
								<TextField
									{...fieldConfig.props}
									bind:value={formData[field.api_name]}
								/>
							{:else if fieldConfig.component === TextareaField}
								<TextareaField
									{...fieldConfig.props}
									bind:value={formData[field.api_name]}
								/>
							{:else if fieldConfig.component === SelectField}
								<SelectField
									{...fieldConfig.props}
									bind:value={formData[field.api_name]}
								/>
							{/if}
						{/if}
					{/each}
				</div>
			</div>
		{/each}

		<div class="flex justify-end gap-4">
			<Button type="button" variant="outline">Cancel</Button>
			<Button type="submit">Submit</Button>
		</div>
	</form>

	<div class="mt-8 p-4 rounded-lg bg-muted">
		<h3 class="font-semibold mb-2">Form Data (Live Preview)</h3>
		<pre class="text-sm">{JSON.stringify(formData, null, 2)}</pre>
	</div>
</div>
