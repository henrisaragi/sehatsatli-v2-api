# Use the official PHP image with Apache
FROM php:8.2-apache as prod

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html

# Install Laravel dependencies
RUN composer install --ignore-platform-reqs

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy the Apache configuration file
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Create a symbolic link for the storage directory
RUN php artisan storage:link

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
