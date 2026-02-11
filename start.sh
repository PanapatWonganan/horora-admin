#!/bin/bash
set -e

# Create SQLite database if using sqlite and file doesn't exist
if [ "${DB_CONNECTION}" = "sqlite" ] || [ -z "${DB_CONNECTION}" ]; then
    touch database/database.sqlite 2>/dev/null || true
fi

# Clear all caches first
php artisan config:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Publish Filament assets
php artisan filament:assets || true

# Publish Laravel assets
php artisan vendor:publish --tag=laravel-assets --ansi --force || true

# Cache config for performance
php artisan config:cache || true

echo "Running migrations..."
php artisan migrate --force || true

echo "Running seeders..."
php artisan db:seed --force || true

echo "Creating storage link..."
php artisan storage:link || true

echo "Starting PHP server on port ${PORT:-8080}..."
php -S 0.0.0.0:${PORT:-8080} -t public public/router.php
