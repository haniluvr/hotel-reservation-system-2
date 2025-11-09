import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        react({
            include: "**/*.{jsx,tsx}",
            exclude: /(card-21|destination-cards|wavy-background|cta-wavy|rooms-carousel|button|carousel|scroll-expansion-hero|resizable-navbar|navbar)\.tsx$/,
            fastRefresh: false,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
});
