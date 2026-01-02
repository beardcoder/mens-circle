#!/bin/sh
set -eu

ARTISAN="php artisan"

# Optional: run migrations (OFF by default)
if [ "${MIGRATE_ON_START:-false}" = "true" ]; then
  echo "Running database migrations..."
  $ARTISAN migrate --force --no-interaction
fi

# Production caching:
# Do NOT clear/rebuild every boot by default (fast deploys).
# Rebuild only if caches are missing OR if explicitly forced.
if [ "${APP_ENV:-production}" = "production" ]; then
  if [ "${FORCE_OPTIMIZE_CLEAR:-false}" = "true" ]; then
    echo "Clearing caches (forced)..."
    $ARTISAN optimize:clear --no-interaction || true
  fi

  if [ "${OPTIMIZE_ON_START:-true}" = "true" ]; then
    if [ ! -f /app/bootstrap/cache/config.php ] || [ "${FORCE_OPTIMIZE_REBUILD:-false}" = "true" ]; then
      echo "Building caches..."
      $ARTISAN optimize --no-interaction
    else
      echo "Caches already present; skipping optimize."
    fi
  fi
fi

# Storage symlink (only if missing)
if [ ! -L /app/public/storage ]; then
  $ARTISAN storage:link --no-interaction 2>/dev/null || true
fi

# Optional: sitemap generation (OFF by default; can be expensive)
if [ "${GENERATE_SITEMAP_ON_START:-false}" = "true" ]; then
  echo "Generating sitemap..."
  $ARTISAN sitemap:generate --no-interaction
fi
