# Laravel 12 on PHP 8.4 - Optimized for Google Cloud Run
FROM php:8.4-cli AS deps
WORKDIR /app
RUN apt-get update && apt-get install -y git zip libicu-dev libzip-dev && \
    docker-php-ext-install intl zip && \
    rm -rf /var/lib/apt/lists/*
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock artisan ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --no-scripts

FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit --no-fund
COPY . .
COPY --from=deps /app/vendor ./vendor
RUN npm run build

FROM php:8.4-apache
WORKDIR /var/www/html

# Install dependencies and configure PHP/Apache
RUN apt-get update && apt-get install -y curl zip git libpng-dev libicu-dev libzip-dev && \
    docker-php-ext-install pdo_mysql gd intl zip opcache && \
    a2enmod rewrite && \
    rm -rf /var/lib/apt/lists/* && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/conf.d/opcache.ini" && \
    sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/Listen 80/Listen ${PORT:-8080}/g' /etc/apache2/ports.conf && \
    sed -i 's/:80/:${PORT:-8080}/g' /etc/apache2/sites-available/000-default.conf

# Copy application
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=deps /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

# Set permissions and optimize
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache && \
    COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --classmap-authoritative

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080
USER www-data
ENTRYPOINT ["docker-entrypoint.sh"]
