<script lang="ts">
	import { cn } from '@/lib/utils';

	interface Props {
		label?: string;
		name: string;
		description?: string;
		error?: string;
		required?: boolean;
		disabled?: boolean;
		class?: string;
		width?: 25 | 50 | 75 | 100;
		children: import('svelte').Snippet<[{
			id: string;
			name: string;
			required: boolean;
			disabled: boolean;
			'aria-invalid'?: boolean;
			'aria-describedby'?: string;
		}]>;
	}

	let {
		label,
		name,
		description,
		error,
		required = false,
		disabled = false,
		class: className,
		width = 100,
		children
	}: Props = $props();

	const widthClasses = {
		25: 'w-full lg:w-1/4',
		50: 'w-full lg:w-1/2',
		75: 'w-full lg:w-3/4',
		100: 'w-full'
	};

	const inputProps = $derived({
		id: name,
		name,
		required,
		disabled,
		'aria-invalid': error ? true : undefined,
		'aria-describedby': error ? `${name}-error` : description ? `${name}-description` : undefined
	});
</script>

<div class={cn('space-y-2', widthClasses[width], className)}>
	{#if label}
		<label for={name} class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
			{label}
			{#if required}
				<span class="text-destructive">*</span>
			{/if}
		</label>
	{/if}

	{@render children(inputProps)}

	{#if description && !error}
		<p id="{name}-description" class="text-sm text-muted-foreground">
			{description}
		</p>
	{/if}

	{#if error}
		<p id="{name}-error" class="text-sm font-medium text-destructive">
			{error}
		</p>
	{/if}
</div>
