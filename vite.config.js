import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/storefront.css',
                'resources/css/public-design-system.css',
                'resources/css/shop-experience.css',
                'resources/css/home.css',
                'resources/css/blog.css',
                'resources/css/product.css',
                'resources/css/admin.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
