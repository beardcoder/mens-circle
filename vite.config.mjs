import { defineConfig } from 'vite';
import { resolve } from 'node:path';

const sourceRoot = resolve(__dirname, 'packages/mens_circle/Resources/Private/Frontend');
const outputRoot = resolve(__dirname, 'packages/mens_circle/Resources/Public/Build');
const jsRoot = resolve(sourceRoot, 'resources/js');

export default defineConfig({
  root: sourceRoot,
  publicDir: false,
  base: '',
  resolve: {
    alias: {
      '@': jsRoot,
    },
  },
  build: {
    outDir: outputRoot,
    emptyOutDir: true,
    cssCodeSplit: false,
    manifest: true,
    sourcemap: false,
    rollupOptions: {
      input: resolve(sourceRoot, 'resources/js/app.ts'),
      output: {
        entryFileNames: 'main.js',
        chunkFileNames: 'chunks/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return 'main.css';
          }

          return 'assets/[name][extname]';
        },
      },
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
  },
});
