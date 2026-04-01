#!/bin/bash
set -euo pipefail

# Only run in remote Claude Code environments
if [ "${CLAUDE_CODE_REMOTE:-}" != "true" ]; then
  exit 0
fi

echo "Setting up mens-circle development environment..."

cd "${CLAUDE_PROJECT_DIR}"

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-interaction --no-progress

# Set up .env if not present
if [ ! -f ".env" ]; then
  echo "Creating .env from .env.example..."
  cp .env.example .env
fi

# Generate app key if not set
if ! grep -q "^APP_KEY=base64:" .env; then
  echo "Generating application key..."
  php artisan key:generate --no-interaction
fi

# Create SQLite database file if missing
if [ ! -f "database/database.sqlite" ]; then
  echo "Creating SQLite database..."
  touch database/database.sqlite
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Install frontend dependencies
echo "Installing frontend dependencies (bun)..."
bun install

# Build frontend assets
echo "Building frontend assets..."
bun run build

echo "Setup complete."
