#!/bin/bash
set -e

echo "Starting Money Wizardry (Cloud Run ultra-fast)..."

# Ultra-fast startup configuration - skip everything non-essential
export DB_CONNECTION=${DB_CONNECTION:-mysql}
export DB_TIMEOUT=3
export DB_CONNECT_TIMEOUT=5
export CACHE_DRIVER=array
export SESSION_DRIVER=cookie
export QUEUE_CONNECTION=sync
export BROADCAST_DRIVER=log
export LOG_LEVEL=warning
export SKIP_MIGRATIONS=true
export SKIP_OPTIMIZATIONS=true

# Only set essential permissions
echo "Quick permission setup..."
mkdir -p /app/storage/logs /app/bootstrap/cache 2>/dev/null || true
chmod 777 /app/storage/logs /app/bootstrap/cache 2>/dev/null || true

# Start Laravel Octane immediately with minimal workers
echo "Starting Octane immediately..."
exec php artisan octane:frankenphp \
    --host=0.0.0.0 \
    --port="${PORT:-80}" \
    --workers=1 \
    --max-requests=100 \
    --no-interaction
