# Use PHP with required extensions
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    sqlite3 \
    libsqlite3-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Make sure SQLite file exists
RUN mkdir -p /data && touch /data/database.sqlite && chmod -R 777 /data

# Permissions
RUN chown -R www-data:www-data /var/www

# Expose the correct port Render expects
EXPOSE 10000

# Laravel run command
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
