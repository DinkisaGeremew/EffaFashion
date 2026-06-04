# EffaFashion — PHP 8.1 + Apache
FROM php:8.1-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql gd \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Show PHP errors (helps debug — disable after fixing)
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/php.ini

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . /var/www/html/

# Create upload directories
RUN mkdir -p /var/www/html/uploads/products \
             /var/www/html/uploads/avatars \
             /var/www/html/uploads/payments \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/uploads

# Apache config
RUN echo '<Directory /var/www/html>\n\
    Options FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/effafashion.conf \
    && a2enconf effafashion

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
CMD ["apache2-foreground"]
