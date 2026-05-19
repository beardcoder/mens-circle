#!/bin/sh

# Idempotently run pending database migrations and ensure Passport's
# OAuth signing keys exist before Octane workers start serving traffic.
#
# Migrations are required so that the oauth_* tables (and any other
# pending schema changes) are in place. The first deploy after adding
# Passport will create oauth_clients, oauth_access_tokens, etc.; on
# subsequent boots `migrate` is a no-op.
#
# Passport keys are generated only when missing AND not provided via
# the PASSPORT_PRIVATE_KEY / PASSPORT_PUBLIC_KEY env vars. Regenerating
# the keys would invalidate every issued OAuth token, so we never
# overwrite an existing key file.

echo "Running database migrations..."
php artisan migrate --force --no-interaction || true

if [ -z "${PASSPORT_PRIVATE_KEY}" ] && [ ! -f /app/storage/oauth-private.key ]; then
    echo "Generating Passport encryption keys..."
    php artisan passport:keys --no-interaction || true
fi
