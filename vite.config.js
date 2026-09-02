import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
