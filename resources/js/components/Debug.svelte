<script lang="ts">
    import * as Card from '@/components/ui/card';
    import { cn } from '@/lib/utils';
    import { Button } from './ui/button';
    import { CheckIcon, CopyIcon } from 'lucide-svelte';
    import { page } from '@inertiajs/svelte';

    let { class: className, _toggleable = false, ...props }: any = $props();

    let jsonContent = $derived(JSON.stringify({ ...props }, null, 4));
    let isCopiedCheck = $state(false);
    let isCopiedTimeout = 0 as unknown as ReturnType<typeof setTimeout>;
</script>


    <Card.Root class={cn('overflow-auto', className)}>
        <Card.Content class="relative">
            <Button
                class="absolute top-6 right-6"
                onclick={() => {
                    navigator.clipboard.writeText(jsonContent);
                    isCopiedCheck = true;
                    clearTimeout(isCopiedTimeout);
                    isCopiedTimeout = setTimeout(() => (isCopiedCheck = false), 1000);
                }}
            >
                {#if isCopiedCheck}
                    <CheckIcon />
                {:else}
                    <CopyIcon />
                {/if}
            </Button>
            <pre class="w-100">{jsonContent}</pre>
        </Card.Content>
    </Card.Root>

