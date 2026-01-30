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

# Run composer scripts
RUN composer dump-autoload --optimize

# Build assets
RUN npm run build

# Create storage directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Create start script
RUN echo '#!/bin/bash\nphp artisan migrate --force\nphp artisan db:seed --force\nphp -S 0.0.0.0:${PORT:-8080} -t public' > /app/start.sh
RUN chmod +x /app/start.sh

# Expose port
EXPOSE 8080

# Start command using PHP built-in server directly
CMD ["/bin/bash", "/app/start.sh"]
