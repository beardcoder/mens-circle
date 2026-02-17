ARG PHP_IMAGE=serversideup/php:8.5-fpm-nginx

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
# 1) Frontend build (Vite) with Bun
# ----------------------------
FROM oven/bun:1 AS assets
WORKDIR /app

COPY --chown=www-data:www-data --from=vendor /app/vendor vendor
COPY package.json bun.lockb* ./
RUN --mount=type=cache,target=/root/.bun/install/cache \
    bun install --frozen-lockfile

COPY . .
COPY vite.config.ts ./
RUN bun run build

# ----------------------------
# 3) Production image
# ----------------------------
FROM ${PHP_IMAGE} AS production

LABEL maintainer="Markus Sommer"

USER root

# Install system packages: GraphicsMagick for image processing and German locale
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        graphicsmagick \
        locales && \
    # Generate German locale
    echo "de_DE.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen de_DE.UTF-8 && \
    update-locale LANG=de_DE.UTF-8 && \
    # Clean up
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions intl gd exif imagick

USER www-data

# TYPO3 startup scripts (database schema, cache flush/warmup, language warmup)
COPY --chmod=755 ./entrypoint.d/ /etc/entrypoint.d/

# Copy application code
COPY --chown=www-data:www-data . .

# Copy Composer vendor from build stage
COPY --chown=www-data:www-data --from=vendor /app/vendor vendor

# Built assets overlay
COPY --from=assets --chown=www-data:www-data /app/public/_assets ./public/_assets

# Update and Optimize autoloader
RUN composer dump-autoload \
      --no-dev \
      --ignore-platform-reqs \
      --classmap-authoritative \
      --no-interaction
