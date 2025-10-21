#!/bin/bash
set -e

echo "Starting Laravel application initialization..."

# Switch to root for initialization tasks
if [ "$(id -u)" != "0" ]; then
    echo "Running as www-data, switching to root for initialization..."
    exec sudo "$0" "$@"
fi

cd /var/www/html

# Ensure storage and cache directories exist with correct permissions
echo "Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set correct permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Wait for database to be ready (Cloud SQL)
echo "Waiting for database connection..."
timeout=60
counter=0
until php artisan inspire > /dev/null 2>&1 || [ $counter -eq $timeout ]; do
    echo "Database not ready, waiting... ($counter/$timeout)"
    sleep 3
    counter=$((counter + 3))
done

if [ $counter -ge $timeout ]; then
    echo "Warning: Database connection timeout. Proceeding anyway..."
fi

# Run database migrations
# --isolated flag prevents multiple containers from running migrations simultaneously
# --force flag runs migrations without confirmation in production
echo "Running database migrations..."
php artisan migrate --force --isolated || {
    echo "Warning: Migrations failed or already up to date"
}

# Cache Laravel configuration for performance
# This must run after .env is available
echo "Optimizing Laravel for production..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Filament components
php artisan filament:optimize

# Link public storage if needed
php artisan storage:link || echo "Storage already linked or not needed"

echo "Laravel initialization complete!"

# Switch back to www-data and start Apache
echo "Starting Apache web server on port ${PORT:-8080}..."
su-exec www-data apache2-foreground
