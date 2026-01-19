#!/bin/bash

# MoonShine Setup Script
# This script completes the MoonShine integration after composer dependencies are installed

set -e

echo "🌙 MoonShine Integration Setup"
echo "=============================="
echo ""

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "❌ Error: vendor directory not found"
    echo "Please run 'composer install' first"
    exit 1
fi

# Check if moonshine package is installed
if [ ! -d "vendor/moonshine-software/moonshine" ]; then
    echo "❌ Error: MoonShine package not found in vendor"
    echo "Please run 'composer require moonshine/moonshine' first"
    exit 1
fi

echo "✅ Dependencies found"
echo ""

# Install MoonShine
echo "📦 Installing MoonShine..."
php artisan moonshine:install --no-interaction

# Publish config if not already published
if [ ! -f "config/moonshine.php" ]; then
    echo "📝 Publishing MoonShine configuration..."
    php artisan vendor:publish --tag=moonshine-config
fi

# Run migrations
echo "🗄️  Running MoonShine migrations..."
php artisan migrate --force

# Create MoonShine admin user
echo ""
echo "👤 Creating MoonShine admin user..."
echo "Please enter the following details:"
php artisan moonshine:user

# Clear caches
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo ""
echo "✅ MoonShine setup complete!"
echo ""
echo "📍 Access Points:"
echo "   Filament:  http://localhost/admin"
echo "   MoonShine: http://localhost/moonshine"
echo ""
echo "📚 Documentation: docs/moonshine-migration.md"
echo ""
echo "🚀 Start development server with: composer run dev"
