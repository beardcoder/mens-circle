#!/bin/sh
set -eu

ARTISAN="php artisan"
APP_DIR="/var/www/html"

cd "$APP_DIR"

# SQLite bootstrap (optional)
if [ "${DB_CONNECTION:-}" = "sqlite" ] || [ -z "${DB_CONNECTION:-}" ]; then
  : "${DB_DATABASE:=$APP_DIR/database/database.sqlite}"
  mkdir -p "$(dirname "$DB_DATABASE")"
  [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
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

# Production caching
if [ "${APP_ENV:-production}" = "production" ]; then
  if [ "${FORCE_OPTIMIZE_CLEAR:-false}" = "true" ]; then
    echo "Clearing caches (forced)..."
    $ARTISAN optimize:clear --no-interaction || true
  fi

  if [ "${OPTIMIZE_ON_START:-true}" = "true" ]; then
    if [ ! -f "$APP_DIR/bootstrap/cache/config.php" ] || [ "${FORCE_OPTIMIZE_REBUILD:-false}" = "true" ]; then
      echo "Building caches..."
      $ARTISAN optimize --no-interaction
    else
      echo "Caches already present; skipping optimize."
    fi
  fi
fi

# Storage symlink (only if missing)
if [ ! -L "$APP_DIR/public/storage" ]; then
  $ARTISAN storage:link --no-interaction 2>/dev/null || true
fi

# Optional: sitemap generation (OFF by default)
if [ "${GENERATE_SITEMAP_ON_START:-false}" = "true" ]; then
  echo "Generating sitemap..."
  $ARTISAN sitemap:generate --no-interaction
fi

echo "Laravel initialization complete."
