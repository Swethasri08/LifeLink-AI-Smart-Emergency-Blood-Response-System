# Blood Donation Management System - Docker Configuration
FROM php:8.1-apache

# Install MySQL extension
RUN docker-php-ext-install mysqli

# Set working directory
WORKDIR /var/www/html

# Copy all application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Apache configuration
RUN a2enmod rewrite

# Start Apache
CMD ["apache2-foreground"]
