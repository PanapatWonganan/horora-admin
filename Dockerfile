FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy package.json
COPY package.json package-lock.json* ./

# Install npm dependencies
RUN npm install

# Copy the rest of the application
COPY . .

# Copy .env.example as default .env (will be overridden by Railway env vars)
RUN cp .env.example .env || true

# Generate app key if not set
RUN php artisan key:generate --force || true

# Run composer scripts
RUN composer dump-autoload --optimize

# Build assets
RUN npm run build

# Publish Filament assets
RUN php artisan filament:assets || true
RUN php artisan vendor:publish --tag=laravel-assets --ansi --force || true

# Create storage directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Set permissions
RUN chmod -R 775 storage bootstrap/cache
RUN chmod +x start.sh

# Expose port
EXPOSE 8080

# Start command
CMD ["bash", "start.sh"]
