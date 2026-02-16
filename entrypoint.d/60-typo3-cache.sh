#!/bin/sh
# Flush and warm up all TYPO3 caches after deployment.
# Ensures stale cache entries from previous builds are purged
# and the new codebase is fully cached before serving requests.

set -e

TYPO3="${APP_BASE_DIR:-/var/www/html}/vendor/bin/typo3"

echo "TYPO3: Flushing caches..."
"$TYPO3" cache:flush

echo "TYPO3: Warming up caches..."
"$TYPO3" cache:warmup
