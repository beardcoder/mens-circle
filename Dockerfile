# syntax=docker/dockerfile:1.7-labs

ARG PHP_IMAGE=serversideup/php:8.5-fpm-nginx

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
# 2) PHP dependencies (Composer)
# ----------------------------
FROM ${PHP_IMAGE} AS vendor
WORKDIR /var/www/html

USER root

# Install composer
RUN install-php-extensions @composer

COPY --chown=www-data:www-data . .

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
# 3) Production image
# ----------------------------
FROM ${PHP_IMAGE} AS production
WORKDIR /var/www/html

USER root

# Install PostgreSQL 18 client from official repository
RUN apt-get update && apt-get install -y --no-install-recommends \
      gnupg \
      lsb-release \
      curl \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /usr/share/keyrings/postgresql-keyring.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/postgresql-keyring.gpg] http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends postgresql-client-18 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP extensions
RUN install-php-extensions \
      pcntl \
      pdo_sqlite \
      pdo_pgsql \
      pgsql \
      intl \
      gd \
      zip \
      bcmath \
      exif

# Custom PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Nginx configuration for asset caching
COPY docker/nginx/cache.conf /etc/nginx/server-opts.d/cache.conf

# App from vendor stage (already contains vendor/)
COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html

# Built assets overlay
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Optimize autoloader
RUN composer dump-autoload \
      --no-dev \
      --ignore-platform-reqs \
      --classmap-authoritative \
      --no-interaction

# Ensure runtime dirs exist with correct ownership
RUN mkdir -p \
      /var/www/html/storage/app/public \
      /var/www/html/storage/framework/cache/data \
      /var/www/html/storage/framework/sessions \
      /var/www/html/storage/framework/views \
      /var/www/html/storage/logs \
      /var/www/html/bootstrap/cache \
      /var/www/html/database \
    && chown -R www-data:www-data \
      /var/www/html/storage \
      /var/www/html/bootstrap/cache \
      /var/www/html/database \
      /var/www/html/public \
    && chmod -R 775 \
      /var/www/html/storage \
      /var/www/html/bootstrap/cache \
      /var/www/html/database

# Laravel startup script (runs after default scripts)
COPY --chmod=755 docker/entrypoint.d/ /etc/entrypoint.d/

# S6 Overlay service for queue worker
COPY --chmod=755 docker/s6-overlay/ /etc/s6-overlay/

# Environment configuration
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=error \
    PHP_OPCACHE_ENABLE=1 \
    SSL_MODE=off

# Switch back to www-data for security
USER www-data

EXPOSE 8080
