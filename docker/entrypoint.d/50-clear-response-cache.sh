#!/bin/sh

# Clear response cache on container startup
# This ensures fresh cache after deployments

echo "Clearing response cache..."
php artisan responsecache:clear || true
echo "Response cache cleared successfully"
