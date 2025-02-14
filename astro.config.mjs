// @ts-check
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'astro/config'

import preact from '@astrojs/preact'

// https://astro.build/config
export default defineConfig({
  experimental: {
    responsiveImages: true,
  },

  vite: {
    plugins: [tailwindcss()],
  },

  integrations: [preact()],
})
