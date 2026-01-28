import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.ts'],
      refresh: true,
    }),
  ],
  server: {
    watch: {
      ignored: ['**/storage/framework/views/**'],
    },
    // Respond to all network requests
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    // Defines the origin of the generated asset URLs during development,
    // this must be set to the Vite dev server URL and selected port.
    origin: `${process.env.VITE_SERVER_URI}`,
    // Configure CORS securely for the Vite dev server to allow requests
    // from *.ddev.site domains, supports additional hostnames (via regex).
    // If you use another `project_tld`, adjust this value accordingly.
    cors: {
      origin: /https?:\/\/([A-Za-z0-9.-]+)?(\.ddev\.site)(?::\d+)?$/,
    },
  },
});
