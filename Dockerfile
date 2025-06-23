# ---------- Node Stage: Build Frontend Assets ----------
FROM node:18 AS node

WORKDIR /app

# Copy only the necessary files for dependency installation
COPY package.json yarn.lock vite.config.* /app/

# Install frontend dependencies using Yarn
RUN yarn install

# Copy frontend source files
COPY resources /app/resources

# Build frontend assets (assumes Vite)
RUN yarn build

# ---------- PHP Stage: Backend Setup ----------
FROM php:8.2-fpm

# Install PHP dependencies and required libraries
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

# Set the working directory
WORKDIR /var/www

# Copy backend files (Laravel app)
COPY . /var/www

# Copy built frontend assets from the node stage into the Laravel public directory
COPY --from=node /app/resources/dist /var/www/public/build

# Install PHP dependencies (production-ready)
RUN composer install --no-dev --optimize-autoloader

# Ensure SQLite DB file exists (optional)
RUN mkdir -p /data && touch /data/database.sqlite && chmod -R 777 /data

# Fix permissions for Laravel
RUN chown -R www-data:www-data /var/www

# Expose the port expected by Render or other platforms
EXPOSE 10000

# Start the Laravel app
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
