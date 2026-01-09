# ===============================
# SmartBook - Laravel (backend/) on Railway
# Root Dockerfile (builds backend folder)
# ===============================

FROM php:8.2-apache

# Enable Apache modules needed for Laravel
RUN a2enmod rewrite headers

# System deps + PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip curl ca-certificates \
    libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# -------------------------------
# IMPORTANT: Copy the whole backend first
# so that `artisan` exists when composer runs scripts (package:discover)
# -------------------------------
COPY backend/ .

# Install PHP deps (Laravel will run package:discover successfully now)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Build frontend assets if your Laravel backend includes package.json/vite/mix
# If your backend doesn't need Node build, you can comment these 2 lines.
RUN npm ci || npm install
RUN npm run build

# Storage/cache directories + permissions
RUN mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache \
 && chmod -R 775 bootstrap/cache storage \
 && chown -R www-data:www-data storage bootstrap/cache

# Set Laravel public as Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Make sure Apache listens on 80 (Railway routes traffic to container port)
RUN echo "Listen 80" > /etc/apache2/ports.conf \
 && printf '%s\n' \
'<VirtualHost *:80>' \
'    ServerAdmin webmaster@localhost' \
'    DocumentRoot /var/www/html/public' \
'    <Directory /var/www/html/public>' \
'        Options -Indexes +FollowSymLinks' \
'        AllowOverride All' \
'        Require all granted' \
'    </Directory>' \
'    ErrorLog ${APACHE_LOG_DIR}/error.log' \
'    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
'</VirtualHost>' \
> /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
