FROM php:8.0-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory to Apache document root
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create uploads directory with proper permissions
RUN mkdir -p /var/www/html/backend/uploads \
    && chmod -R 777 /var/www/html/backend/uploads

# Set up Apache configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy the backend files to the document root
RUN cp -r /var/www/html/backend/* /var/www/html/ \
    && rm -rf /var/www/html/backend \
    && rm -rf /var/www/html/frontend

# Setup MySQL connection wait script
COPY wait-for-mysql.sh /usr/local/bin/wait-for-mysql.sh
RUN chmod +x /usr/local/bin/wait-for-mysql.sh

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 