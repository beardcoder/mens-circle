import * as Sentry from '@sentry/astro'

Sentry.init({
  dsn: 'https://78668d19e59854f5d2b8b4733b8b39a1@o4508569353977856.ingest.de.sentry.io/4508850417238096',
  tracesSampleRate: 1.0,
  environment: 'production',
})
