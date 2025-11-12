<script lang="ts">
	import FieldBase from './FieldBase.svelte';
	import { Input } from '@/components/ui/input';

	interface Props {
		label?: string;
		name: string;
		value?: string;
		description?: string;
		error?: string;
		required?: boolean;
		disabled?: boolean;
		placeholder?: string;
		type?: 'text' | 'email' | 'password' | 'tel' | 'url';
		width?: 25 | 50 | 75 | 100;
		class?: string;
		onchange?: (value: string) => void;
	}

	let {
		label,
		name,
		value = $bindable(''),
		description,
		error,
		required = false,
		disabled = false,
		placeholder,
		type = 'text',
		width = 100,
		class: className,
		onchange
	}: Props = $props();

	function handleInput(e: Event) {
		const target = e.target as HTMLInputElement;
		value = target.value;
		onchange?.(value);
	}
</script>

<FieldBase {label} {name} {description} {error} {required} {disabled} {width} class={className}>
	{#snippet children(props)}
		<Input
			{...props}
			{type}
			{placeholder}
			{value}
			oninput={handleInput}
		/>
	{/snippet}
</FieldBase>
