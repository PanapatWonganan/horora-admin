#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force || true

echo "Running seeders..."
php artisan db:seed --force || true

echo "Starting PHP server on port ${PORT:-8080}..."
php -S 0.0.0.0:${PORT:-8080} -t public
