# Use the official PHP image as a base
FROM php:7.4-apache

# Install required PHP extensions including GD, ZIP, and Intl
RUN apt-get update && \
    apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip libicu-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd zip intl pdo pdo_mysql && \
    docker-php-ext-enable gd zip intl

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html
# Define build-time argument for LATEST_TAG
ARG LATEST_TAG=
# Set environment variable LATEST_TAG from build-time argument
ENV LATEST_TAG=${LATEST_TAG}
# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html
# Set permissions for the web directory
RUN chown -R www-data:www-data /var/www/html
RUN docker-php-ext-install opcache
# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]



