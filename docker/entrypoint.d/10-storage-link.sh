#!/bin/sh

# Ensure the public/storage symlink exists so uploaded media is accessible.

echo "Ensuring storage link..."
php artisan storage:link --quiet || true
