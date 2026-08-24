FROM php:8.3-apache

# Install PostgreSQL PDO driver and system dependencies for Dompdf & PhpSpreadsheet
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd zip

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory and copy app files
WORKDIR /var/www/html
COPY . .

# Install PHP dependencies without development packages
RUN composer install --no-dev --optimize-autoloader

# Expose standard Apache port
EXPOSE 80

CMD ["apache2-foreground"]