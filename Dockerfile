# syntax=docker/dockerfile:1.7-labs

ARG FRANKENPHP_IMAGE=dunglas/frankenphp:1-php8.5-alpine

# ----------------------------
# 1) Frontend build (Vite) with Bun
# ----------------------------
FROM oven/bun:1-slim AS assets
WORKDIR /app

COPY package.json bun.lock* ./
RUN --mount=type=cache,target=/root/.bun/install/cache \
    bun install --frozen-lockfile

COPY resources/ resources/
COPY vite.config.ts ./
COPY public/ public/
RUN bun run build


# ----------------------------
# 2) PHP dependencies (Composer) using same PHP as prod
# ----------------------------
FROM ${FRANKENPHP_IMAGE} AS vendor
WORKDIR /app

RUN apk add --no-cache git unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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

RUN composer dump-autoload \
    --no-dev \
    --ignore-platform-reqs \
    --classmap-authoritative \
    --no-interaction


# ----------------------------
# 3) Production image (FrankenPHP + Octane, HTTP/3 ready)
# ----------------------------
FROM ${FRANKENPHP_IMAGE} AS production

# Install required PHP extensions:
# - intl:    required by Filament
# - gd:      image driver for Spatie Media Library (default)
# - exif:    EXIF data extraction from images
# - opcache: bytecode cache for production performance
# - pcntl:   signal handling for Octane (SIGINT/SIGTERM)
# - redis:   phpredis native client for CACHE_STORE=redis
RUN install-php-extensions \
    intl \
    gd \
    exif \
    opcache \
    pcntl \
    redis

# pdo_pgsql: build from PHP source against plain libpq-dev.
# install-php-extensions would pull postgresql18-dev, which drags in
# llvm20/clang20 (Postgres 18 JIT toolchain) — 668 MB of build deps we
# don't need. libpq-dev alone is enough for the PDO driver.
RUN apk add --no-cache --virtual .pdo-pgsql-build libpq-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql \
    && apk del --purge .pdo-pgsql-build \
    && apk add --no-cache libpq

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=error \
    OCTANE_SERVER=frankenphp \
    OCTANE_HOST=0.0.0.0 \
    OCTANE_PORT=80 \
    OCTANE_ADMIN_PORT=2019 \
    OCTANE_WORKERS=auto \
    OCTANE_MAX_REQUESTS=500 \
    OCTANE_HTTPS=false \
    OCTANE_HTTP_REDIRECT=false \
    APP_BASE_PATH=/app \
    APP_PUBLIC_PATH=/app/public

WORKDIR /app

# PHP runtime tuning (opcache, JIT, memory, timezone)
COPY --chmod=644 docker/php/app.ini /usr/local/etc/php/conf.d/zz-app.ini

# App + vendor from vendor stage (already contains vendor/)
COPY --from=vendor --chown=root:root /app /app

# Built frontend assets
COPY --from=assets --chown=root:root /app/public/build /app/public/build

# Startup hooks (clear response cache, sitemap, etc.) + entrypoint
COPY --chmod=755 docker/entrypoint.d/ /docker-entrypoint.d/
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/docker-entrypoint

# Expose HTTP (80), HTTPS over TCP (443, HTTP/1.1 + HTTP/2),
# HTTPS over UDP/QUIC (443, HTTP/3), and Caddy admin (2019)
EXPOSE 80 443 443/udp 2019

ENTRYPOINT ["docker-entrypoint"]
