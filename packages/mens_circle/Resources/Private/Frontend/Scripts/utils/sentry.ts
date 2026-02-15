import * as Sentry from '@sentry/browser';

const dsn = import.meta.env.VITE_SENTRY_DSN as string | undefined;

if (dsn) {
  Sentry.init({
    dsn,
    environment: (import.meta.env.VITE_SENTRY_ENVIRONMENT as string) || 'production',
    release: (import.meta.env.VITE_SENTRY_RELEASE as string) || undefined,
    tracesSampleRate: 0,
    autoSessionTracking: false,
  });
}

export { Sentry };
