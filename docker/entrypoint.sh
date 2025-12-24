#!/bin/sh
set -e

# Ensure storage directories exist and have correct permissions
mkdir -p /app/storage/app/public
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/logs
mkdir -p /app/bootstrap/cache

# Set permissions
chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/public
chmod -R 775 /app/storage /app/bootstrap/cache /app/public

# Ensure SQLite database exists (if using SQLite)
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /app/database
    touch /app/database/database.sqlite
    chown www-data:www-data /app/database/database.sqlite
    chmod 664 /app/database/database.sqlite
fi

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY is not set. Generating a new key..."
    php artisan key:generate --force
fi

# Cache configuration for production
if [ "$APP_ENV" = "production" ]; then 
    echo "Clearing existing caches..."
    php artisan optimize:clear

    echo "Caching configuration for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    php artisan optimize
fi

# Run migrations if MIGRATE_ON_START is set
if [ "${MIGRATE_ON_START:-false}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Create storage symlink
php artisan storage:link --force 2>/dev/null || true

# Generate Sitemap
php artisan sitemap:generate

# Start Supervisor to manage all processes (FrankenPHP + queue workers)
echo "Starting Supervisor to manage FrankenPHP and queue workers..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
