FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Redis extension melalui PECL
RUN pecl install redis && docker-php-ext-enable redis

# Dapatkan Composer versi terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Expose port 9000 untuk PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]