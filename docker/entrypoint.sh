#!/bin/sh
set -eu

ARTISAN="php artisan"

# Ensure expected dirs exist (no recursive chmod/chown every boot)
mkdir -p \
  /app/storage/app/public \
  /app/storage/framework/cache/data \
  /app/storage/framework/sessions \
  /app/storage/framework/views \
  /app/storage/logs \
  /app/bootstrap/cache \
  /app/database

# SQLite bootstrap (optional)
if [ "${DB_CONNECTION:-}" = "sqlite" ] || [ -z "${DB_CONNECTION:-}" ]; then
  : "${DB_DATABASE:=/app/database/database.sqlite}"
  mkdir -p "$(dirname "$DB_DATABASE")"
  [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
  chown www-data:www-data "$DB_DATABASE" || true
  chmod 664 "$DB_DATABASE" || true
fi

# APP_KEY handling:
# - In production: fail fast if missing (runtime generation breaks scaling)
# - In non-prod: generate if missing
if [ -z "${APP_KEY:-}" ]; then
  if [ "${APP_ENV:-production}" = "production" ]; then
    echo >&2 "ERROR: APP_KEY is not set. Refusing to start in production."
    exit 1
  else
    echo "APP_KEY missing; generating for non-production..."
    $ARTISAN key:generate --force --no-interaction
  fi
fi

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
      $ARTISAN optimize --force --no-interaction
    else
      echo "Caches already present; skipping optimize."
    fi
  fi
fi

# Storage symlink (only if missing)
if [ ! -L /app/public/storage ]; then
  $ARTISAN storage:link --force --no-interaction 2>/dev/null || true
fi

# Optional: sitemap generation (OFF by default; can be expensive)
if [ "${GENERATE_SITEMAP_ON_START:-false}" = "true" ]; then
  echo "Generating sitemap..."
  $ARTISAN sitemap:generate --no-interaction
fi

echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
