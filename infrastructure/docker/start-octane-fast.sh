#!/bin/bash
set -e

echo "Starting Money Wizardry (Cloud Run optimized)..."

# Ensure proper permissions for Laravel directories
echo "Setting up permissions..."
mkdir -p /app/storage/logs /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true
chown -R mw:mw /app/storage /app/bootstrap/cache 2>/dev/null || true

# Skip slow operations for Cloud Run
echo "Skipping database checks and optimizations for faster startup..."

# Start Laravel Octane with FrankenPHP immediately
echo "Starting Octane..."
exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port="${PORT:-80}" \
    --workers="${OCTANE_WORKERS:-1}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-500}" \
    --no-interaction
