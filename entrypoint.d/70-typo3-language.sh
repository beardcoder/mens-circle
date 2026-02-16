#!/bin/sh
# Warm up TYPO3 language caches so translations are available immediately.

set -e

TYPO3="${APP_BASE_DIR:-/var/www/html}/vendor/bin/typo3"

echo "TYPO3: Warming up language caches..."
"$TYPO3" language:cache:warmup
