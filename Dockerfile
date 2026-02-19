# syntax=docker/dockerfile:1.7-labs

ARG PHP_IMAGE=serversideup/php:8.4-fpm-nginx

# ----------------------------
# 1) Frontend build (Vite) with Bun
# ----------------------------
FROM oven/bun:1-slim AS assets
WORKDIR /app

COPY package.json bun.lockb* ./
RUN --mount=type=cache,target=/root/.bun/install/cache \
    bun install --frozen-lockfile

COPY resources/ resources/
COPY vite.config.ts ./
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

# Install PHP intl extension (required by Filament)
USER root
RUN install-php-extensions intl
USER www-data

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=error \
    PHP_DATE_TIMEZONE=Europe/Berlin \
    PHP_MEMORY_LIMIT=512M \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_JIT=1 \
    PHP_OPCACHE_JIT_BUFFER_SIZE=64M \
    AUTORUN_ENABLED=true \
    HEALTHCHECK_PATH=/up \
    NGINX_FASTCGI_BUFFERS="16 16k" \
    NGINX_FASTCGI_BUFFER_SIZE="32k" \
    NGINX_WEBROOT=/app/public \
    APP_BASE_DIR=/app
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

# Copy startup scripts to clear response cache on container start
USER root
COPY --chmod=755 docker/entrypoint.d/ /etc/entrypoint.d/
USER www-data
