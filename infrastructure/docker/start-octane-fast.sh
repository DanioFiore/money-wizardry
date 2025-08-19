#!/bin/bash
set -e

echo "Starting Money Wizardry (Cloud Run optimized)..."

# Cloud Run specific environment variables
export CLOUD_RUN_SERVICE=true
export DB_TIMEOUT=2
export DB_CONNECT_TIMEOUT=2
export CACHE_DRIVER=array
export SESSION_DRIVER=cookie
export QUEUE_CONNECTION=sync
export BROADCAST_DRIVER=log
export LOG_LEVEL=error
export LOG_CHANNEL=stderr

# Force skip database for initial health check (Cloud Run optimization)
export SKIP_DATABASE_HEALTH_CHECK=true
export SKIP_MIGRATIONS=true

# Cloud Run memory optimizations
export PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT:-512M}
export OCTANE_GC_ENABLED=${OCTANE_GC_ENABLED:-true}

# Ensure proper permissions for Laravel directories (Cloud Run optimized)
echo "Setting up permissions (fast mode)..."
mkdir -p /app/storage/logs /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views /app/storage/app
# Use more efficient permission setting for Cloud Run
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true

# Skip database entirely for Cloud Run if needed
if [ "${SKIP_DATABASE:-false}" = "true" ]; then
    echo "Database connections disabled for Cloud Run..."
    export DB_CONNECTION=array
fi

# Cloud Run port detection and validation
PORT=${PORT:-8080}
echo "Cloud Run detected - using port: $PORT"

# Validate Cloud Run environment
if [ -n "$K_SERVICE" ]; then
    echo "Running in Cloud Run service: $K_SERVICE"
    echo "Cloud Run revision: ${K_REVISION:-unknown}"
    echo "Cloud Run configuration: ${K_CONFIGURATION:-unknown}"
fi

# Cloud Run signal handling for graceful shutdown
trap 'echo "Received shutdown signal, gracefully stopping..."; kill -TERM $!; wait $!' SIGTERM SIGINT

# Performance monitoring for Cloud Run
if [ "${APP_ENV}" = "production" ]; then
    echo "Production mode - enabling performance monitoring"
    export OCTANE_WATCH=false
else
    echo "Development mode detected"
fi

# Start Laravel Octane with FrankenPHP (Cloud Run optimized)
echo "Starting Octane with Cloud Run configuration..."
echo "Workers: ${OCTANE_WORKERS:-1}, Max requests: ${OCTANE_MAX_REQUESTS:-500}"

# Cloud Run health check pre-warming (async)
(
    sleep 3
    echo "Pre-warming Cloud Run instance..."
    for i in {1..3}; do
        curl -s -m 2 "http://localhost:${PORT}/health" > /dev/null 2>&1 && break || sleep 1
    done
    echo "Pre-warming completed"
) &

# Start Octane with Cloud Run specific settings
exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port="${PORT}" \
    --workers="${OCTANE_WORKERS:-1}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-500}" \
    --no-interaction
