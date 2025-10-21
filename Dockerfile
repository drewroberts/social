# Dockerfile for Laravel 12 on PHP 8.4 - Google Cloud Run
# Multi-stage build: PHP deps, Node.js for Vite assets, Production stage for runtime

# ============================================
# PHP Dependencies Stage
# ============================================
FROM composer:2.7 AS php-deps

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies with better optimization
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-suggest \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative

# ============================================
# Builder Stage: Node.js for Vite assets
# ============================================
FROM node:22-alpine AS builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node dependencies
RUN npm ci --no-audit --no-fund

# Copy application source (needed for Vite build)
COPY . .

# Copy PHP vendor dependencies (needed for CSS imports)
COPY --from=php-deps /app/vendor ./vendor

# Build production assets with Vite
RUN npm run build

# ============================================
# Production Stage: PHP 8.4 with Apache
# ============================================
FROM php:8.4-apache

LABEL maintainer="Drew Roberts"
LABEL description="Laravel 12 Social Media Management Platform"

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions in a single layer
RUN apt-get update && apt-get install -y \
    # Essential system packages
    curl \
    zip \
    unzip \
    git \
    # Required libraries for PHP extensions
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    libjpeg-dev \
    libfreetype6-dev \
    # Redis extension dependencies (for better caching)
    libhiredis-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    # Install PHP extensions required by Laravel 12
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        sockets \
    # Install Redis extension for better session/cache performance
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Enable Apache modules
    && a2enmod rewrite headers expires deflate \
    # Clean up to reduce image size
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy optimized PHP configuration
COPY docker/php-production.ini $PHP_INI_DIR/conf.d/

# Configure Apache DocumentRoot to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure Apache security and performance
RUN { \
        echo "ServerTokens Prod"; \
        echo "ServerSignature Off"; \
        echo "TraceEnable Off"; \
        echo "Header always set X-Content-Type-Options nosniff"; \
        echo "Header always set X-Frame-Options DENY"; \
        echo "Header always set X-XSS-Protection \"1; mode=block\""; \
        echo "Header always set Referrer-Policy \"strict-origin-when-cross-origin\""; \
        echo "Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains\""; \
    } >> /etc/apache2/conf-available/security.conf \
    && a2enconf security

# Enable compression and caching
RUN { \
        echo "<IfModule mod_deflate.c>"; \
        echo "    AddOutputFilterByType DEFLATE text/css text/javascript application/javascript application/json"; \
        echo "    AddOutputFilterByType DEFLATE text/html text/plain text/xml application/xml application/xhtml+xml"; \
        echo "    AddOutputFilterByType DEFLATE image/svg+xml application/rss+xml application/atom_xml"; \
        echo "</IfModule>"; \
        echo "<IfModule mod_expires.c>"; \
        echo "    ExpiresActive On"; \
        echo "    ExpiresByType text/css \"access plus 1 year\""; \
        echo "    ExpiresByType application/javascript \"access plus 1 year\""; \
        echo "    ExpiresByType image/png \"access plus 1 year\""; \
        echo "    ExpiresByType image/jpg \"access plus 1 year\""; \
        echo "    ExpiresByType image/jpeg \"access plus 1 year\""; \
        echo "    ExpiresByType image/gif \"access plus 1 year\""; \
        echo "    ExpiresByType image/svg+xml \"access plus 1 year\""; \
        echo "</IfModule>"; \
    } > /etc/apache2/conf-available/performance.conf \
    && a2enconf performance

# Copy composer files
COPY composer.json composer.lock ./

# Copy PHP dependencies from php-deps stage
COPY --from=php-deps /app/vendor ./vendor

# Using COMPOSER_ALLOW_SUPERUSER to avoid warnings in container
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy application code (excluding files in .dockerignore)
COPY . .

# Copy built assets from builder stage
COPY --from=builder /app/public/build ./public/build

# Create storage directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache,testing} \
    && mkdir -p storage/{app,logs} \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache \
    && chmod -R 775 storage/framework storage/logs

# Run Composer scripts and optimize
RUN composer dump-autoload --optimize --classmap-authoritative \
    && composer check-platform-reqs --no-dev

# Pre-cache what we can without environment variables
# Generate optimized class loader and package discovery
RUN php artisan package:discover --ansi \
    && php artisan event:cache \
    && php artisan filament:cache-components

# Copy and set up entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 8080 (Cloud Run default)
EXPOSE 8080

# Configure Apache to listen on PORT environment variable (Cloud Run requirement)
RUN sed -i 's/Listen 80/Listen ${PORT:-8080}/g' /etc/apache2/ports.conf \
    && sed -i 's/:80/:${PORT:-8080}/g' /etc/apache2/sites-available/000-default.conf

# Add a simple health check script
RUN echo '#!/bin/sh\nexec curl -f http://localhost:${PORT:-8080}/ -H "User-Agent: Docker-Health-Check" --max-time 5 --retry 0 --silent --show-error --fail' > /usr/local/bin/healthcheck.sh \
    && chmod +x /usr/local/bin/healthcheck.sh

# Health check configuration
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD /usr/local/bin/healthcheck.sh

# Switch to non-root user for security (will be overridden in entrypoint for initialization)
USER www-data

# Use entrypoint script to run migrations and start Apache
ENTRYPOINT ["docker-entrypoint.sh"]
