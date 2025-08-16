#!/bin/bash
set -e

echo "Starting Money Wizardry..."

# Ensure proper permissions for Laravel directories
echo "Setting up permissions..."
mkdir -p /app/storage/logs /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R mw:mw /app/storage /app/bootstrap/cache

# Wait for database to be ready (if using MySQL)
if [ "${DB_CONNECTION}" = "mysql" ] && [ "${CLOUD_RUN_SERVICE:-}" = "" ]; then
    echo "Waiting for MySQL to be ready..."
    timeout=30
    while ! mysqladmin ping -h"${DB_HOST}" --silent && [ $timeout -gt 0 ]; do
        sleep 1
        timeout=$((timeout-1))
    done
    if [ $timeout -eq 0 ]; then
        echo "Warning: MySQL connection timeout, continuing anyway..."
    else
        echo "MySQL is ready!"
    fi
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Run Laravel optimizations
echo "Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Laravel Octane with FrankenPHP
echo "Starting Octane..."
exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port="${PORT:-80}" \
    --workers="${OCTANE_WORKERS:-auto}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-1000}" \
    --no-interaction
