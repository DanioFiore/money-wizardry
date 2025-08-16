
# =============================================================================
# MULTI-STAGE BUILD FOR LARAVEL OCTANE WITH FRANKENPHP
# =============================================================================

# -----------------------------------------------------------------------------
# STAGE 1: BUILDER STAGE
# Purpose: Install dependencies, compile assets, and prepare application
# -----------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.3 AS builder

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
COPY composer.json ./

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
RUN npm install

# Copy application source code
COPY . .

# The permission 755 is an octal notation that defines file or directory permissions in Unix/Linux systems.
# First digit: Owner permissions
# Second digit: Group permissions
# Third digit: Other users permissions

# Each digit is the sum of:
# 4 = Read permission
# 2 = Write permission
# 1 = Execute permission

# 7 (Owner): 4+2+1 = Read + Write + Execute
# 5 (Group): 4+0+1 = Read + Execute (no write)
# 5 (Others): 4+0+1 = Read + Execute (no write)

# This permission is typically used for:
# Executable files (scripts, binaries)
# Directories (where execute permission allows entering the directory)
# Public scripts that should be readable and runnable by others but only modifiable by the owner
# In the Dockerfile context, we're likely setting permissions for files or directories being copied into the container image.

# Set proper permissions for Laravel directories
# Storage and cache directories need write access
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache

# Build and optimize assets
# Laravel Vite compilation for production
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

# groupadd -g 1000 mw - Creates a new group named "mw"
# -g 1000 assigns group ID 1000 to mw
# Group ID 1000 is commonly used for the first non-system user group

# useradd -u 1000 -g mw -m mw - Creates a new user named "mw"
# -u 1000 assigns user ID 1000 (matches the group ID)
# -g mw sets the primary group to the "mw" group created above
# -m creates a home directory for the user (/home/mw)

# usermod -a -G www-data mw - Modifies the existing "mw" user
# -a -G www-data adds the user to the "www-data" group as a supplementary group
# The "www-data" group is typically used by web servers (Apache, Nginx) for file permissions

# Why This Setup Matters
# Security & Permissions: Running processes as a non-root user is a security best practice. The user ID 1000 often matches the developer's local user ID, making file permission handling smoother between the container and host system.
# Web Server Integration: Adding the user to the www-data group allows your application to interact properly with web server processes that also run under this group.

# Create application user for security
# Running as non-root user reduces security risks
RUN groupadd -g 1000 mw \
    && useradd -u 1000 -g mw -m mw \
    && usermod -a -G www-data mw

# Set working directory
WORKDIR /app


# --from=builder - Copies from a previous build stage named "builder" instead of the build context. This indicates our previous stage
# --chown=mw:mw - Sets ownership of copied files to user mw and group mw
# /app /app - Copies from app directory in the builder stage to app directory in the current stage

# Builder stage: Install dependencies and build assets (npm, webpack, etc.)
# Production stage: Copy only the built artifacts, not the build tools
# Security & Performance Benefits
# Smaller final image: Build tools aren't included in the final image
# Proper ownership: Files are owned by mw user instead of root, improving security
# Clean separation: Build environment is separate from runtime environment
# The mw user is likely a non-root user created earlier in the Dockerfile for running the application securely.

# Copy application from builder stage
# This includes optimized vendor/, built assets, and cached configs
COPY --from=builder --chown=mw:mw /app /app

# Copy optimized PHP configuration
# Custom php.ini for production optimizations
COPY --chown=mw:mw infrastructure/docker/99-laravel.ini /usr/local/etc/php/conf.d/99-laravel.ini

# Create OPcache preload file for Laravel
COPY --chown=mw:mw infrastructure/docker/opcache-preload.php /app/config/opcache-preload.php

# Create Octane configuration for FrankenPHP
COPY --chown=mw:mw infrastructure/docker/octane.php /app/config/octane.php

# Set up FrankenPHP Caddyfile for Laravel Octane
COPY --chown=mw:mw infrastructure/docker/Caddyfile /etc/caddy/Caddyfile

# Create startup script for Laravel Octane
COPY --chown=mw:mw infrastructure/docker/start-octane.sh /app/start-octane.sh

# Make startup script executable
RUN chmod +x /app/start-octane.sh

# Set proper file ownership and permissions
RUN chown -R mw:mw /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache

# Create health check script
COPY --chown=mw:mw infrastructure/docker/health-check.sh /app/health-check.sh

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

