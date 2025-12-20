# Build Stage: Install dependencies and build assets
FROM node:24-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package.json package-lock.json* ./

# Install npm dependencies
RUN npm ci

# Copy source files for build
COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/

# Build assets
RUN npm run build


# PHP Dependencies Stage
FROM composer:2 AS composer-builder

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies without dev packages
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# Copy application code for post-install scripts
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize --no-dev


# Production Stage: FrankenPHP
FROM dunglas/frankenphp:1-php8.5-alpine AS production

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    sqlite \
    icu-libs \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    libzip \
    oniguruma

# Install PHP extensions
RUN install-php-extensions \
    pcntl \
    pdo_sqlite \
    intl \
    opcache \
    gd \
    zip \
    bcmath \
    exif

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy PHP configuration
COPY docker/php/php.ini "$PHP_INI_DIR/conf.d/99-custom.ini"
COPY docker/php/opcache.ini "$PHP_INI_DIR/conf.d/opcache.ini"

# Copy application from composer stage
COPY --from=composer-builder /app/vendor /app/vendor

# Copy application code
COPY . /app

# Copy built assets from node stage
COPY --from=node-builder /app/public/build /app/public/build

# Create required directories
RUN mkdir -p \
    /app/storage/app/public \
    /app/storage/framework/cache/data \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache \
    /app/database

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database
RUN chmod -R 775 /app/storage /app/bootstrap/cache /app/database

# Configure FrankenPHP Caddyfile
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Set environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV LOG_LEVEL=error
ENV OCTANE_SERVER=frankenphp

# Expose port
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start FrankenPHP with Octane
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=80", "--admin-port=2019"]
