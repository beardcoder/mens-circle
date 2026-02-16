#!/bin/sh
# Warm up TYPO3 language update

set -e

TYPO3="${APP_BASE_DIR:-/var/www/html}/vendor/bin/typo3"

echo "TYPO3: language update..."
"$TYPO3" language:update
