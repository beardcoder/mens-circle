#!/bin/sh

# Clear response cache on container startup.
# Sitemap generation runs on the daily schedule (routes/console.php),
# not on startup, so a freshly booted container does not need the DB yet.

echo "Clearing response cache..."
php artisan responsecache:clear || true
