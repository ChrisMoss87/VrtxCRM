import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
            buildDirectory: 'build',
        }),
        svelte({
            inspector: {
                toggleKeyCombo: 'alt-x',
                showToggleButton: 'always',
                toggleButtonPos: 'bottom-right',
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        host: 'localhost',
        port: 5173,
        headers: {
            'Access-Control-Allow-Origin': '*', // this is only for the local dev server so it can allow all
        },
        allowedHosts: ['harmony.local'],
    },
});
