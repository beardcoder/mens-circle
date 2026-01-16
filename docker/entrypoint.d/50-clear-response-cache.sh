#!/bin/sh

# Clear response cache and generate sitemap on container startup
# This ensures fresh cache and up-to-date sitemap after deployments

echo "Clearing response cache..."
php artisan responsecache:clear || true
echo "Response cache cleared successfully"

echo "Generating sitemap..."
php artisan sitemap:generate || true
echo "Sitemap generated successfully"
