#!/bin/bash
set -e

echo "Starting Laravel application in Cloud Run..."

# Only run database operations if we have a database connection configured
if [ -n "${DB_CONNECTION:-}" ] && [ "${DB_CONNECTION}" != "sqlite" ]; then
    echo "Checking database connectivity..."
    # Use a more lightweight database check with timeout
    timeout 30 php artisan db:show --database="${DB_CONNECTION}" >/dev/null 2>&1 || {
        echo "Database connection failed or timed out. Continuing without database operations."
    }
else
    echo "No database connection configured, skipping database operations."
fi

# Cache configuration and routes for better performance
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
php artisan storage:link || true

echo "Starting Apache server on port ${PORT:-8080}..."

# Ensure Apache is configured for the correct port
PORT=${PORT:-8080}
sed -i "s/Listen 8080/Listen $PORT/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/:8080/:$PORT/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Start Apache
echo "Apache starting on port $PORT"
exec apache2-foreground
