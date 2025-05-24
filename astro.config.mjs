// @ts-check
import preact from '@astrojs/preact'
import sitemap from '@astrojs/sitemap'
import sentry from '@sentry/astro'
import tailwindcss from '@tailwindcss/vite'
import yeskunallumami from '@yeskunall/astro-umami'
import { defineConfig } from 'astro/config'

// https://astro.build/config
export default defineConfig({
  site: 'https://mens-circle.de',
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
    yeskunallumami({
      id: '9384afba-8736-46df-a418-642b3ec39742',
      endpointUrl: 'https://tracking.letsbenow.de',
      autotrack: true,
      domains: ['mens-circle.de'],
    }),
    sitemap(),
  ],
})
