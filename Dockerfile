
# =============================================================================
# MULTI-STAGE BUILD FOR LARAVEL OCTANE WITH FRANKENPHP
# =============================================================================

# -----------------------------------------------------------------------------
# STAGE 1: BUILDER STAGE
# Purpose: Install dependencies, compile assets, and prepare application
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:latest AS builder

# Install system dependencies required for building
# - git: for composer dependencies from git repos
# - unzip: for composer package extraction
# - nodejs & npm: for asset compilation
# - python3 & build-essential: for native modules compilation
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    nodejs \
    npm \
    python3 \
    build-essential \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install additional PHP extensions needed for Laravel
# - pdo_mysql: Database connectivity
# - redis: Session/cache storage
# - bcmath: Precision mathematics
# - gd: Image manipulation
# - intl: Internationalization
# - zip: Archive handling
RUN install-php-extensions \
    pdo_mysql \
    redis \
    bcmath \
    gd \
    intl \
    zip \
    opcache \
    pcntl

# Set working directory for build stage
WORKDIR /app

# Copy composer files first for better Docker layer caching
# This allows composer install to be cached if composer files haven't changed
COPY composer.json composer.lock ./

# Install PHP dependencies with optimizations
# --no-dev: Skip development dependencies
# --optimize-autoloader: Generate optimized autoloader
# --no-scripts: Skip post-install scripts for security
# --prefer-dist: Prefer distribution packages over source
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --prefer-dist \
    --no-progress \
    --no-interaction

# Copy package.json files for Node.js dependencies
COPY package*.json ./

# Install Node.js dependencies
# Include dev dependencies needed for building assets
# --no-audit: Skip security audit for faster builds
RUN npm ci --no-audit

# Copy application source code
COPY . .

# Set proper permissions for Laravel directories
# Storage and cache directories need write access
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache

# Build and optimize assets  
# Laravel Mix/Vite compilation for production
# Add node_modules/.bin to PATH and run npm build
ENV PATH="/app/node_modules/.bin:$PATH"
RUN npm run build

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# -----------------------------------------------------------------------------
# STAGE 2: PRODUCTION STAGE
# Purpose: Create minimal, secure, optimized production image
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.3 AS production

# Metadata labels for the image
LABEL maintainer="daniofioredev@gmail.com" \
        version="1.0" \
        description="Money Wizardry Production Image"

# Install only essential runtime dependencies
# Minimizing attack surface by installing only what's needed
RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean \
    && apt-get autoremove -y

# Install required PHP extensions for production
RUN install-php-extensions \
    pdo_mysql \
    redis \
    bcmath \
    gd \
    intl \
    zip \
    opcache \
    pcntl

# Create application user for security
# Running as non-root user reduces security risks
RUN groupadd -g 1000 mw \
    && useradd -u 1000 -g mw -m mw \
    && usermod -a -G www-data mw

# Set working directory
WORKDIR /app

# Copy application from builder stage
# This includes optimized vendor/, built assets, and cached configs
COPY --from=builder --chown=mw:mw /app /app

# Copy optimized PHP configuration
# Custom php.ini for production optimizations
COPY --chown=mw:mw <<EOF /usr/local/etc/php/conf.d/99-laravel.ini
; =============================================================================
; LARAVEL OCTANE PHP OPTIMIZATION CONFIGURATION
; =============================================================================

; Memory Management
memory_limit = 512M
max_execution_time = 30

; OPcache Configuration for Maximum Performance
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 32
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 0
opcache.enable_file_override = 1
opcache.optimization_level = 0x7FFFFFFF
opcache.preload = /app/config/opcache-preload.php

; Realpath Cache Optimization
realpath_cache_size = 4M
realpath_cache_ttl = 3600

; File Upload Limits
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 20

; Session Configuration (disabled for Octane)
session.auto_start = 0

; Error Handling for Production
display_errors = 0
display_startup_errors = 0
log_errors = 1
error_log = /proc/self/fd/2

; Security Settings
expose_php = 0
allow_url_fopen = 0
allow_url_include = 0

; Performance Settings
max_input_vars = 10000
default_socket_timeout = 10
EOF

# Create OPcache preload file for Laravel
COPY --chown=mw:mw <<EOF /app/config/opcache-preload.php
<?php
/**
 * OPcache Preload Script for Laravel Octane
 * Preloads core Laravel files into memory for better performance
 */

if (function_exists('opcache_compile_file')) {
    // Preload Composer autoloader
    opcache_compile_file('/app/vendor/autoload.php');
    
    // Preload Laravel core files
    \$files = array(
        '/app/vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
        '/app/vendor/laravel/framework/src/Illuminate/Container/Container.php',
        '/app/vendor/laravel/framework/src/Illuminate/Support/ServiceProvider.php',
        '/app/vendor/laravel/framework/src/Illuminate/Http/Request.php',
        '/app/vendor/laravel/framework/src/Illuminate/Http/Response.php',
    );
    
    foreach (\$files as \$file) {
        if (file_exists(\$file)) {
            opcache_compile_file(\$file);
        }
    }
}
EOF

# Create Octane configuration for FrankenPHP
COPY --chown=mw:mw <<EOF /app/config/octane.php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    */
    'server' => env('OCTANE_SERVER', 'frankenphp'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    */
    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    */
    'listeners' => [
        \Laravel\Octane\Listeners\WorkerErrorOccurred::class => [
            \Laravel\Octane\Listeners\ReportException::class,
            \Laravel\Octane\Listeners\StopWorkerIfNecessary::class,
        ],
        \Laravel\Octane\Listeners\WorkerStarting::class => [
            \Laravel\Octane\Listeners\EnsureUploadedFilesAreValid::class,
            \Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved::class,
        ],
        \Laravel\Octane\Listeners\WorkerStopping::class => [],
        \Laravel\Octane\Listeners\RequestReceived::class => [],
        \Laravel\Octane\Listeners\RequestHandled::class => [],
        \Laravel\Octane\Listeners\RequestTerminated::class => [
            \Laravel\Octane\Listeners\FlushLogContext::class,
        ],
        \Laravel\Octane\Listeners\TaskReceived::class => [],
        \Laravel\Octane\Listeners\TaskTerminated::class => [
            \Laravel\Octane\Listeners\FlushLogContext::class,
        ],
        \Laravel\Octane\Listeners\TickReceived::class => [],
        \Laravel\Octane\Listeners\TickTerminated::class => [
            \Laravel\Octane\Listeners\FlushLogContext::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm / Flush Bindings
    |--------------------------------------------------------------------------
    */
    'warm' => [
        'auth',
        'cache',
        'cache.store',
        'config',
        'db',
        'log',
        'queue',
        'request',
        'router',
        'session',
        'session.store',
        'view',
    ],

    'flush' => [
        'auth',
        'session',
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Cache Table
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'rows' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Watching
    |--------------------------------------------------------------------------
    */
    'watch' => [
        'app',
        'config',
        'resources/views',
        'routes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection
    |--------------------------------------------------------------------------
    */
    'garbage_collection' => [
        'enabled' => env('OCTANE_GC_ENABLED', true),
        'app_requests' => env('OCTANE_GC_APP_REQUESTS', 10000),
        'task_requests' => env('OCTANE_GC_TASK_REQUESTS', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Execution Time
    |--------------------------------------------------------------------------
    */
    'max_execution_time' => 30,
];
EOF

# Set up FrankenPHP Caddyfile for Laravel Octane
COPY --chown=mw:mw <<EOF /etc/caddy/Caddyfile
{
    # Global FrankenPHP configuration
    frankenphp {
        # Worker configuration for Laravel Octane
        worker /app/public/index.php
        num_threads {$FRANKENPHP_NUM_THREADS:auto}
    }
    
    # Security headers
    header {
        # HSTS
        Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
        # Prevent MIME sniffing
        X-Content-Type-Options "nosniff"
        # XSS Protection
        X-Frame-Options "DENY"
        # Referrer Policy
        Referrer-Policy "strict-origin-when-cross-origin"
        # Content Security Policy (adjust as needed)
        Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"
        # Remove server information
        -Server
        -X-Powered-By
    }
}

# Main server block
{$SERVER_NAME:localhost} {
    # Document root
    root * /app/public
    
    # Enable Gzip compression
    encode gzip
    
    # Security: Hide sensitive files
    @forbidden {
        path /.env*
        path /.git/*
        path /config/*
        path /storage/*
        path /vendor/*
        path /*.md
        path /composer.*
        path /package.*
        path /artisan
    }
    respond @forbidden 404

    # Handle PHP files with FrankenPHP
    php_fastcgi unix//var/run/php/php-fpm.sock
    
    # Static file handling with caching
    @static {
        file
        path *.css *.js *.ico *.png *.jpg *.jpeg *.gif *.svg *.woff *.woff2 *.ttf *.eot
    }
    header @static {
        Cache-Control "public, max-age=31536000, immutable"
        Expires "1 year"
    }
    
    # Laravel routing
    try_files {path} {path}/ /index.php?{query}
    
    # Health check endpoint
    handle /health {
        respond "OK" 200
    }
    
    # Access logs (optional, comment out for production)
    # log {
    #     output file /app/storage/logs/access.log
    #     format json
    # }
}
EOF

# Create startup script for Laravel Octane
COPY --chown=mw:mw <<'EOF' /app/start-octane.sh
#!/bin/bash
set -e

echo "Starting Money Wizardry..."

# Ensure proper permissions for Laravel directories
echo "Setting up permissions..."
mkdir -p /app/storage/logs /app/storage/framework/cache /app/storage/framework/sessions /app/storage/framework/views
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R mw:mw /app/storage /app/bootstrap/cache

# Wait for database to be ready (if using MySQL)
if [ "${DB_CONNECTION}" = "mysql" ]; then
    echo "Waiting for MySQL to be ready..."
    while ! mysqladmin ping -h"${DB_HOST}" --silent; do
        sleep 1
    done
    echo "MySQL is ready!"
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
    --port=80 \
    --workers="${OCTANE_WORKERS:-auto}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-1000}" \
    --no-interaction
EOF

# Make startup script executable
RUN chmod +x /app/start-octane.sh

# Set proper file ownership and permissions
RUN chown -R mw:mw /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache

# Create health check script
COPY --chown=mw:mw <<'EOF' /app/health-check.sh
#!/bin/bash
# Health check script for Laravel Octane
set -e

# Check if the application responds
if curl -f -s http://localhost/health > /dev/null 2>&1; then
    echo "Health check passed"
    exit 0
else
    echo "Health check failed"
    exit 1
fi
EOF

RUN chmod +x /app/health-check.sh

# Switch to non-root user for security
USER mw

# Expose port 80 (FrankenPHP default)
EXPOSE 80

# Environment variables with sensible defaults
ENV OCTANE_WORKERS=auto \
    OCTANE_MAX_REQUESTS=1000 \
    OCTANE_GC_ENABLED=true \
    FRANKENPHP_NUM_THREADS=auto \
    PHP_MEMORY_LIMIT=512M \
    PHP_MAX_EXECUTION_TIME=30

# Health check configuration
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD ["/app/health-check.sh"]

# Set the default command to start Laravel Octane
CMD ["/app/start-octane.sh"]

# =============================================================================
# BUILD INSTRUCTIONS:
# 
# Build: docker build -t laravel-octane-app .
# Run: docker run -p 8080:80 -e DB_HOST=mysql -e DB_PASSWORD=secret laravel-octane-app
# 
# Environment variables to configure:
# - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# - REDIS_HOST, REDIS_PASSWORD
# - APP_KEY, APP_ENV, APP_DEBUG
# - OCTANE_WORKERS, OCTANE_MAX_REQUESTS
# - FRANKENPHP_NUM_THREADS
# =============================================================================

