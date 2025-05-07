FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Enable Apache modules
RUN a2enmod rewrite headers

# Copy the application
COPY . .

# Create necessary directories with proper permissions
RUN mkdir -p backend/uploads backend/logs && \
    chmod -R 777 backend/uploads backend/logs

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies
RUN if [ -f "composer.json" ]; then composer install --no-interaction --no-dev; fi

# Configure Apache for Render
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf && \
    sed -i 's/\/var\/www\/html/\/var\/www\/html\/backend/g' /etc/apache2/sites-available/000-default.conf

# Set the document root to the backend directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/backend

# Expose the port Render will use
EXPOSE $PORT

# Start Apache with the port from Render's environment variable
CMD sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && \
    apache2-foreground 