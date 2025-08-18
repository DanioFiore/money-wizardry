#!/bin/bash
set -e

echo "Starting Money Wizardry (Cloud Run optimized)..."

# Set environment variables for faster startup
export DB_TIMEOUT=2
export DB_CONNECT_TIMEOUT=3
export CACHE_DRIVER=array
export SESSION_DRIVER=cookie
export QUEUE_CONNECTION=sync
export BROADCAST_DRIVER=log
export LOG_LEVEL=error

# Force skip database for initial health check
export SKIP_DATABASE_HEALTH_CHECK=true

# Ensure proper permissions for Laravel directories
echo "Setting up permissions..."
mkdir -p /app/storage/logs /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true
chown -R mw:mw /app/storage /app/bootstrap/cache 2>/dev/null || true

# Skip slow operations for Cloud Run
echo "Skipping database checks and optimizations for faster startup..."

# Check if we should skip database entirely (for testing)
if [ "${SKIP_DATABASE:-false}" = "true" ]; then
    echo "Database connections disabled for testing..."
    export DB_CONNECTION=array
fi

# Start Laravel Octane with FrankenPHP immediately
echo "Starting Octane with minimal configuration..."

# Pre-warm the application in background while starting the server
(
    sleep 2
    echo "Pre-warming application..."
    curl -s http://localhost:${PORT:-80}/health > /dev/null 2>&1 || true
) &

exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port="${PORT:-80}" \
    --workers="${OCTANE_WORKERS:-1}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-500}" \
    --no-interaction
