# syntax=docker/dockerfile:1.7-labs

ARG PHP_IMAGE=serversideup/php:8.5-frankenphp

# ----------------------------
# 1) Frontend build (Vite) with Bun
# ----------------------------
FROM oven/bun:1-slim AS assets
WORKDIR /app

COPY package.json bun.lockb* ./
RUN --mount=type=cache,target=/root/.bun/install/cache \
    bun install --frozen-lockfile

COPY resources/ resources/
COPY vite.config.mjs ./
COPY public/ public/
RUN bun run build


# ----------------------------
# 2) PHP dependencies (Composer) using same PHP as prod
# ----------------------------
FROM ${PHP_IMAGE} AS vendor
WORKDIR /app

# Note: serversideup/php images come with Composer pre-installed
COPY . .

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
      --no-dev \
      --no-interaction \
      --no-progress \
      --no-scripts \
      --prefer-dist \
      --ignore-platform-reqs \
      --optimize-autoloader

# ----------------------------
# 3) Production image (FrankenPHP)
# ----------------------------
FROM ${PHP_IMAGE} AS production

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=error \
    OCTANE_SERVER=frankenphp \
    CADDY_SERVER_ROOT=/app/public \
    APP_BASE_DIR=/app

# Switch to root for installation tasks
USER root

# Only runtime OS deps (keep it slim)
# Install PostgreSQL 18 client and supervisor
RUN apt-get update && apt-get install -y --no-install-recommends \
      gnupg \
      lsb-release \
      supervisor \
      curl \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /usr/share/keyrings/postgresql-keyring.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/postgresql-keyring.gpg] http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends postgresql-client-18 \
    && rm -rf /var/lib/apt/lists/*

# Note: serversideup/php images include many common extensions by default
# We only install what's missing (most are already included)
RUN install-php-extensions \
      pdo_pgsql \
      pgsql

USER www-data
WORKDIR /app

# Environment defaults for production

# App + vendor from vendor stage (already contains vendor/)
# Use --chown to avoid heavy chown layers later
COPY --from=vendor --chown=www-data:www-data /app /app

# Built assets overlay
COPY --from=assets --chown=www-data:www-data /app/public/build /app/public/build

# Update and Optimize autoloader
RUN composer dump-autoload \
      --no-dev \
      --ignore-platform-reqs \
      --classmap-authoritative \
      --no-interaction

# Ensure runtime dirs exist (do it once at build time)
RUN mkdir -p \
      /app/storage/app/public \
      /app/storage/framework/cache/data \
      /app/storage/framework/sessions \
      /app/storage/framework/views \
      /app/storage/logs \
      /app/bootstrap/cache

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--port=8080", "--host=0.0.0.0"]

