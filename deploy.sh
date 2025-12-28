#!/bin/bash
set -e

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart Octane
sudo systemctl restart octane

echo "Deployment complete!"
