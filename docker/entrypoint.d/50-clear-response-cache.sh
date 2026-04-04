#!/bin/sh

# Clear response cache and generate sitemap on container startup

echo "Clearing response cache..."
php artisan responsecache:clear || true

echo "Generating sitemap..."
php artisan sitemap:generate || true
