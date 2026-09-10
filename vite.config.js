import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                // The workspace ships its own small behaviour file. It has no
                // dependencies and never loads on the public site, so it stays
                // out of the three.js/gsap chunking below.
                'resources/js/admin.js',
            ],
            refresh: ['resources/views/**', 'resources/content/**'],
        }),
        tailwindcss(),
    ],

    build: {
        // three.js is large and cached independently of our application code.
        // Splitting it keeps app.js small enough to parse before first paint.
        rollupOptions: {
            output: {
                manualChunks: {
                    three: ['three'],
                    motion: ['gsap', 'gsap/ScrollTrigger'],
                },
            },
        },
        target: 'es2020',
        chunkSizeWarningLimit: 900,
    },

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
