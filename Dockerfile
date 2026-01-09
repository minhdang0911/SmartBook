# =========================================
# SmartBook - Laravel backend (DEBUG)
# Backend source nằm trong thư mục /backend
# =========================================

FROM php:8.2-apache

# Enable Apache modules
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

# =========================================
# COPY TOÀN BỘ BACKEND TRƯỚC
# =========================================
COPY backend/ .

# ===== DEBUG – XÁC NHẬN ĐANG DÙNG ĐÚNG DOCKERFILE =====
RUN echo "===== USING ROOT DOCKERFILE /Dockerfile ====="
RUN echo "===== LIST FILES IN /var/www/html ====="
RUN ls -la

# ===== DEBUG – KIỂM TRA FILE artisan =====
RUN test -f artisan || (echo "❌ ERROR: artisan NOT FOUND after COPY backend/" && exit 1)
RUN echo "✅ artisan FOUND"

# =========================================
# Composer install (artisan đã tồn tại)
# =========================================
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Frontend build (nếu có)
RUN npm ci || npm install
RUN npm run build

# Permissions cho Laravel
RUN mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache \
 && chmod -R 775 bootstrap/cache storage \
 && chown -R www-data:www-data storage bootstrap/cache

# Apache document root → public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Apache vhost
RUN echo "Listen 80" > /etc/apache2/ports.conf \
 && printf '%s\n' \
'<VirtualHost *:80>' \
'  ServerAdmin webmaster@localhost' \
'  DocumentRoot /var/www/html/public' \
'  <Directory /var/www/html/public>' \
'    Options -Indexes +FollowSymLinks' \
'    AllowOverride All' \
'    Require all granted' \
'  </Directory>' \
'  ErrorLog ${APACHE_LOG_DIR}/error.log' \
'  CustomLog ${APACHE_LOG_DIR}/access.log combined' \
'</VirtualHost>' \
> /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]
