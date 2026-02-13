ARG PHP_IMAGE=serversideup/php:8.5-fpm-nginx

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

# Update and Optimize autoloader
RUN composer install \
      --no-dev \
      --no-interaction \
      --no-progress \
      --prefer-dist \
      --ignore-platform-reqs \
      --optimize-autoloader