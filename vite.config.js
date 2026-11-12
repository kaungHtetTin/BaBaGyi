import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    publicDir: false,
    build: { target: 'es2022' },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'public/client/app.js',
                'public/client/app.css',
            ],
            refresh: true,
        }),
    ],
});
