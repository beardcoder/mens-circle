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
      dsn: 'https://0abd9376a58cd41a6e446ace623d3792@o4508569353977856.ingest.de.sentry.io/4508569413550160',
      tracesSampleRate: 1.0,
      sourceMapsUploadOptions: {
        project: 'mens-circle',
        authToken: process.env.SENTRY_AUTH_TOKEN,
      },
    }),
  ],

  output: 'server',
  adapter: vercel({ webAnalytics: { enabled: true } }),
})
