# ==============================================
# Stage 1: Base PHP with common extensions
# ==============================================
FROM php:8.3-cli-alpine AS base

# Install system dependencies (common to both dev and prod)
RUN apk add --no-cache \
    mysql-client \
    postgresql-dev \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    oniguruma \
    icu-libs

# ==============================================
# Stage 2: Development image
# Simple PHP CLI with extensions - no nginx, no opcache
# Uses Laravel's built-in dev server
# ==============================================
FROM base AS development

# Install build dependencies for PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apk del .build-deps

WORKDIR /var/www/html

EXPOSE 8000

# Laravel's built-in dev server - auto-detects file changes
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ==============================================
# Stage 3: Composer dependencies (for production)
# ==============================================
FROM composer:2 AS composer

WORKDIR /app

# Copy composer files first (better layer caching)
COPY composer.json composer.lock ./

# Install dependencies without dev packages
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --ignore-platform-reqs

# Copy entire application
COPY . .

# Generate optimized autoloader (skip scripts - they need full Laravel env)
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ==============================================
# Stage 4: Build frontend assets (for production)
# (needs vendor/ for Flux CSS)
# ==============================================
FROM node:20-alpine AS frontend

WORKDIR /app

# Copy package files first (better layer caching)
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci

# Copy source files needed for Vite build
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public

# Copy vendor directory from composer stage (needed for Flux CSS!)
COPY --from=composer /app/vendor ./vendor

# Build production assets
RUN npm run build

# ==============================================
# Stage 5: Production image
# Full stack with nginx, php-fpm, supervisor, opcache
# ==============================================
FROM php:8.3-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    postgresql-dev \
    libpng \
    libjpeg-turbo \
    freetype \
    libzip \
    oniguruma \
    icu-libs

# Install build dependencies and PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && apk del .build-deps

# Configure OPcache for production performance
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Copy application from composer stage (includes vendor/)
COPY --from=composer /app /var/www/html

# Copy built frontend assets from frontend stage
COPY --from=frontend /app/public/build /var/www/html/public/build

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create required directories and set permissions
RUN mkdir -p /var/log/supervisor \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Expose port 80 for nginx
EXPOSE 80

# Start supervisor (which manages nginx + php-fpm)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
