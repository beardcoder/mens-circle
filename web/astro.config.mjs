import { defineConfig } from 'astro/config';
import bun from '@wyattjoh/astro-bun-adapter';

export default defineConfig({
  output: 'server',
  adapter: bun(),
  server: {
    port: 4400,
  },
  vite: {
    css: {
      preprocessorOptions: {},
    },
  },
});
