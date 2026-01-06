FROM php:8.2-apache

# 1) Enable Apache modules
RUN a2enmod rewrite headers

# 2) Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    fontconfig \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# 3) Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 4) Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

# 5) Copy application files
COPY . .

# 6) Run post-install scripts
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi || true

# 7) Install and build frontend assets
RUN npm ci || npm install
RUN npm run build

# 8) Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9) Configure Apache to point to Laravel's public directory
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/|/var/www/html/public|g' /etc/apache2/apache2.conf

# 10) Set Apache to listen on PORT environment variable
RUN echo "Listen \${PORT}" > /etc/apache2/ports.conf

# 11) Update virtual host to use PORT
RUN sed -i 's|<VirtualHost \*:80>|<VirtualHost *:${PORT}>|g' /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT}

# 12) Start Apache
CMD ["apache2-foreground"]
