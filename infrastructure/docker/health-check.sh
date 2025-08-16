#!/bin/bash
# Health check script for Laravel Octane
set -e

# Check if the application responds
# Use the same port that the application is using
PORT=${PORT:-80}
if curl -f -s "http://localhost:${PORT}/health" > /dev/null 2>&1; then
    echo "Health check passed"
    exit 0
else
    echo "Health check failed"
    exit 1
fi
