# Stage 1: Build dependencies
FROM composer:2 AS composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Production image
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    && docker-php-ext-install pdo_mysql

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

# Copy application files
COPY --from=composer /app /var/www/html

# Copy configuration files
COPY .docker/nginx.conf /etc/nginx/http.d/default.conf
COPY .docker/supervisord.conf /etc/supervisor.d/app.ini
COPY .docker/php.ini /usr/local/etc/php/conf.d/app.ini

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create SQLite database directory
RUN mkdir -p /var/www/html/database && touch /var/www/html/database/database.sqlite
RUN chown -R www-data:www-data /var/www/html/database

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
