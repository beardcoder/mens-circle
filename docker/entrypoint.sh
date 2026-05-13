#!/bin/sh
set -e

# Run startup hook scripts (response cache clear, sitemap, etc.)
if [ -d /docker-entrypoint.d ]; then
    for f in /docker-entrypoint.d/*.sh; do
        [ -r "$f" ] || continue
        echo "Running $f..."
        sh "$f"
    done
fi

# If the container is invoked with arguments (e.g. `queue:work`), run those
# instead of Octane so the same image can serve queue workers / schedulers.
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

set -- \
    --host="${OCTANE_HOST:-0.0.0.0}" \
    --port="${OCTANE_PORT:-80}" \
    --admin-port="${OCTANE_ADMIN_PORT:-2019}" \
    --workers="${OCTANE_WORKERS:-auto}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-500}" \
    --caddyfile="${OCTANE_CADDYFILE:-/app/docker/Caddyfile}"

if [ "${OCTANE_HTTPS:-false}" = "true" ]; then
    set -- "$@" --https
    if [ "${OCTANE_HTTP_REDIRECT:-false}" = "true" ]; then
        set -- "$@" --http-redirect
    fi
fi

if [ -n "${OCTANE_LOG_LEVEL:-}" ]; then
    set -- "$@" --log-level="${OCTANE_LOG_LEVEL}"
fi

exec php artisan octane:frankenphp "$@"
