#!/bin/sh
# Update TYPO3 database schema (safe operations only: add + change)
# Runs on every container start to apply pending migrations.

set -e

TYPO3="${APP_BASE_DIR:-/var/www/html}/vendor/bin/typo3"

echo "TYPO3: Setting up extensions..."
"$TYPO3" extension:setup
