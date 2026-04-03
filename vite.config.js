import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'resources/views/**',
                'routes/**',
                'resources/css/**',
                'app/**',
            ],
        }),
        tailwindcss(),
    ],
    // Use 5174 so we do not collide with NativePHP/Electron’s own Vite on 5173 when running `composer native:dev`.
    server: {
        host: '127.0.0.1',
        port: 5174,
        strictPort: false,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
