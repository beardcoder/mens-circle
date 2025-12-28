# syntax=docker/dockerfile:1.7-labs

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.5

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

# Composer (keeps PHP version aligned with final stage)
RUN install-php-extensions @composer

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
      --no-dev \
      --no-interaction \
      --no-progress \
      --prefer-dist \
      --ignore-platform-req \
      --optimize-autoloader

# Copy the app source after vendor install so vendor layer stays cached
COPY . .

# Make autoloader as fast as possible in production
RUN composer dump-autoload \
      --no-dev \
      --classmap-authoritative \
      --no-interaction


# ----------------------------
# 3) Production image (FrankenPHP)
# ----------------------------
FROM ${PHP_IMAGE} AS production
WORKDIR /app

# Only runtime OS deps (keep it slim)
RUN apt-get update && apt-get install -y --no-install-recommends \
      sqlite3 \
      postgresql-client \
      supervisor \
      curl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN install-php-extensions \
      pcntl \
      pdo_sqlite \
      pdo_pgsql \
      pgsql \
      intl \
      opcache \
      gd \
      zip \
      bcmath \
      exif

# PHP config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/php.ini     "$PHP_INI_DIR/conf.d/99-custom.ini"
COPY docker/php/opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"

# App + vendor from vendor stage (already contains vendor/)
# Use --chown to avoid heavy chown layers later
COPY --from=vendor --chown=www-data:www-data /app /app

# Built assets overlay
COPY --from=assets --chown=www-data:www-data /app/public/build /app/public/build

# Ensure runtime dirs exist (do it once at build time)
RUN mkdir -p \
      /app/storage/app/public \
      /app/storage/framework/cache/data \
      /app/storage/framework/sessions \
      /app/storage/framework/views \
      /app/storage/logs \
      /app/bootstrap/cache \
      /app/database \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database /app/public \
    && chmod -R 775 /app/storage /app/bootstrap/cache /app/database

# Supervisor + Caddy/FrankenPHP config
RUN mkdir -p /var/log/supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/Caddyfile /app/Caddyfile

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=error \
    OCTANE_SERVER=frankenphp

EXPOSE 80

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Simple healthcheck (adjust path to your health route if you have one)
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s \
  CMD curl -fsS http://127.0.0.1:80/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
