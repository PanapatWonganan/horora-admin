#!/bin/bash
set -e

# Create SQLite database if using sqlite and file doesn't exist
if [ "${DB_CONNECTION}" = "sqlite" ] || [ -z "${DB_CONNECTION}" ]; then
    touch database/database.sqlite 2>/dev/null || true
fi

# Cache config for performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Running migrations..."
php artisan migrate --force || true

echo "Running seeders..."
php artisan db:seed --force || true

echo "Creating storage link..."
php artisan storage:link || true

echo "Starting PHP server on port ${PORT:-8080}..."
php -S 0.0.0.0:${PORT:-8080} -t public
