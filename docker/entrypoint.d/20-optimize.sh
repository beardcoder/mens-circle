#!/bin/sh

# Warm Laravel's runtime caches so Octane workers boot with compiled
# config/routes/events/views and Filament assets pre-resolved. These caches
# depend on runtime environment variables, so they must be built here rather
# than at image-build time.

echo "Optimizing Laravel caches..."
php artisan optimize:clear --quiet || true
php artisan optimize --quiet || true
php artisan icons:cache --quiet || true
php artisan filament:cache-components --quiet || true
