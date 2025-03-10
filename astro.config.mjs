// @ts-check
import vercel from '@astrojs/vercel/serverless'
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'astro/config'

import preact from '@astrojs/preact'

import sentry from '@sentry/astro'

// https://astro.build/config
export default defineConfig({
  experimental: {
    responsiveImages: true,
  },

  vite: {
    plugins: [tailwindcss()],
  },

  integrations: [
    preact(),
    sentry({
      sourceMapsUploadOptions: {
        project: 'mens-circle',
        authToken: process.env.SENTRY_AUTH_TOKEN,
      },
    }),
  ],

  output: 'server',
  adapter: vercel({}),
})
