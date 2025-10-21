#!/bin/bash
set -e

# Wait for database
until php artisan inspire >/dev/null 2>&1; do
  echo "Waiting for database..."
  sleep 2
done

# Run migrations and optimize
php artisan migrate --force --isolated || true
php artisan optimize
php artisan storage:link || true

# Start Apache
exec apache2-foreground
