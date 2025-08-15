#!/bin/bash
# Health check script for Laravel Octane
set -e

# Check if the application responds
# Why we use localhost here?
# Because the health check script is executed inside the container and Octane listen for 0.0.0.0:80 (all interfaces) so localhost is the correct address to use
if curl -f -s http://localhost/health > /dev/null 2>&1; then
    echo "Health check passed"
    exit 0
else
    echo "Health check failed"
    exit 1
fi
