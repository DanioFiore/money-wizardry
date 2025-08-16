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

# Run database migrations (skip in Cloud Run for faster startup)
if [ "${SKIP_MIGRATIONS:-}" != "true" ]; then
    echo "Running database migrations..."
    timeout 60 php artisan migrate --force || echo "Warning: Migration timeout or failed, continuing..."
fi

# Run Laravel optimizations (skip some for faster startup)
if [ "${SKIP_OPTIMIZATIONS:-}" != "true" ]; then
    echo "Running Laravel optimizations..."
    timeout 30 php artisan config:cache || echo "Config cache skipped"
    timeout 30 php artisan route:cache || echo "Route cache skipped" 
    timeout 30 php artisan view:cache || echo "View cache skipped"
else
    echo "Skipping optimizations for faster startup..."
fi

# Start Laravel Octane with FrankenPHP
echo "Starting Octane..."
exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port="${PORT:-80}" \
    --workers="${OCTANE_WORKERS:-auto}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-1000}" \
    --no-interaction
