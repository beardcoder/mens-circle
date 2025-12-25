import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Production optimizations for better PageSpeed scores
        minify: 'esbuild', // Use esbuild for faster minification
        rollupOptions: {
            output: {
                // Manual chunking for better caching
                manualChunks: (id) => {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
        // Optimize chunk size
        chunkSizeWarningLimit: 1000,
        // Disable source maps in production
        sourcemap: false,
        // CSS code splitting for better caching
        cssCodeSplit: true,
        // Asset inlining threshold (smaller assets will be inlined as base64)
        assetsInlineLimit: 4096,
        // Report compressed size
        reportCompressedSize: true,
        // Target modern browsers for smaller bundles
        target: 'es2020',
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
