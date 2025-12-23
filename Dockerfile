# Build Stage: Install dependencies and build assets
FROM node:24 AS node-builder

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
FROM dunglas/frankenphp:1-php8.5 AS production

# Set working directory
WORKDIR /app

# Install system dependencies for GD
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    supervisor \
    curl && \
    rm -rf /var/lib/apt/lists/*

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
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache /app/database /app/public
RUN chmod -R 775 /app/storage /app/bootstrap/cache /app/database /app/public

# Configure FrankenPHP Caddyfile
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Configure Supervisor
RUN mkdir -p /var/log/supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

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
