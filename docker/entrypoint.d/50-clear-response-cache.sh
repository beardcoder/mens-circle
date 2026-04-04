#!/bin/sh

# Clear response cache, generate sitemap, and warm cache on container startup
# This ensures fresh cache and up-to-date sitemap after deployments

echo "Clearing response cache..."
php artisan responsecache:clear || true
echo "Response cache cleared successfully"

echo "Generating sitemap..."
php artisan sitemap:generate || true
echo "Sitemap generated successfully"

# Warm the response cache by requesting key pages
# This prevents the first visitor from experiencing a slow cold-start
echo "Warming response cache..."
APP_URL="${APP_URL:-http://localhost:8080}"
curl -s -o /dev/null -w "  Homepage: %{http_code} (%{time_total}s)\n" "${APP_URL}/" || true
curl -s -o /dev/null -w "  Next event: %{http_code} (%{time_total}s)\n" "${APP_URL}/event" || true
echo "Cache warming complete"
