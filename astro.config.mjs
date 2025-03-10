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
      dsn: 'https://78668d19e59854f5d2b8b4733b8b39a1@o4508569353977856.ingest.de.sentry.io/4508850417238096',
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
