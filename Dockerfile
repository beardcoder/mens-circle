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
# 3) Production image
# ----------------------------
FROM ${PHP_IMAGE} AS production

LABEL maintainer="Markus Sommer"

USER root
RUN install-php-extensions intl
USER www-data

# Copy application code
COPY --chown=www-data:www-data . .

# Copy Composer vendor from build stage
COPY --chown=www-data:www-data --from=vendor /app/vendor vendor

# Update and Optimize autoloader
RUN composer dump-autoload \
      --no-dev \
      --ignore-platform-reqs \
      --classmap-authoritative \
      --no-interaction
