# multi-stage build to optimize image size
FROM composer:2.6 AS vendor

WORKDIR /tmp/
COPY composer.json composer.lock ./
RUN composer install \
    --no-ansi \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

# final image
FROM dunglas/frankenphp:latest

# install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    redis \
    zip \
    pcntl \
    bcmath \
    exif

# install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# copy vendor from previous stage
COPY --from=vendor /tmp/vendor/ /app/vendor/

RUN echo "APP_ENV=production" > .env && \
    echo "APP_DEBUG=false" >> .env && \
    echo "LOG_CHANNEL=stderr" >> .env

# copy application code
COPY . .

COPY .env.production .env

# optimize Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# exposed port
EXPOSE 8000

# health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s \
    CMD curl -f http://localhost:8000/health || exit 1

# start command
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]