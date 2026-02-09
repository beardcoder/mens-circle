# =============================================================================
# Multi-stage Dockerfile for TYPO3 v14 — Coolify Deployment
# Base: serversideup/php:8.5-fpm-nginx
# =============================================================================

# ---------------------------------------------------------------------------
# Stage 1: Build frontend assets with Bun + Vite
# ---------------------------------------------------------------------------
FROM oven/bun:1 AS frontend

WORKDIR /build

COPY package.json bun.lock ./
RUN bun install --frozen-lockfile

COPY vite.config.mjs tsconfig.json ./
COPY packages/mens_circle/Resources/Private/Frontend packages/mens_circle/Resources/Private/Frontend

RUN bun run build

# ---------------------------------------------------------------------------
# Stage 2: Install PHP dependencies with Composer
# ---------------------------------------------------------------------------
FROM composer:2 AS composer

WORKDIR /build

COPY composer.json composer.lock ./
COPY packages/mens_circle/composer.json packages/mens_circle/composer.json

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# ---------------------------------------------------------------------------
# Stage 3: Production image
# ---------------------------------------------------------------------------
FROM serversideup/php:8.5-fpm-nginx AS production

LABEL maintainer="Markus Sommer"
LABEL description="TYPO3 v14 – Männerkreis Niederbayern / Straubing"

# Install TYPO3-required PHP extensions
USER root
RUN install-php-extensions intl gd zip pdo_mysql opcache bcmath

# Custom nginx config for TYPO3
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY --chown=www-data:www-data . .

# Copy Composer vendor from build stage
COPY --chown=www-data:www-data --from=composer /build/vendor vendor

# Copy built frontend assets from build stage
COPY --chown=www-data:www-data --from=frontend /build/packages/mens_circle/Resources/Public/Build packages/mens_circle/Resources/Public/Build

# Ensure writable directories exist
RUN mkdir -p var/cache var/lock var/log var/session public/typo3temp public/fileadmin config/system \
    && chown -R www-data:www-data var public/typo3temp public/fileadmin config/system

# Remove build-only files from final image
RUN rm -rf node_modules docker .dockerignore .ddev .claude .codex

USER www-data

# PHP-FPM + Nginx via S6 overlay (handled by base image)
EXPOSE 8080
