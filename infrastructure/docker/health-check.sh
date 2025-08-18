#!/bin/bash
# Health check script for Laravel Octane
set -e

# Check if the application responds
# Use the same port that the application is using
PORT=${PORT:-80}

# Try a simple HTTP request with shorter timeout for Cloud Run
if timeout 3 curl -f -s -m 2 "http://localhost:${PORT}/health" > /dev/null 2>&1; then
    echo "Health check passed"
    exit 0
else
    echo "Health check failed, trying root endpoint..."
    # Fallback: try root endpoint
    if timeout 3 curl -f -s -m 2 "http://localhost:${PORT}/" > /dev/null 2>&1; then
        echo "Root endpoint responsive"
        exit 0
    else
        echo "Health check failed completely"
        exit 1
    fi
fi
