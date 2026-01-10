# =========================
# 1) Base image
# =========================
FROM php:8.2-apache

# =========================
# 2) Fix Apache MPM (CRITICAL)
# =========================
RUN a2dismod mpm_event mpm_worker || true \
 && a2enmod mpm_prefork

# Enable Apache modules
RUN a2enmod rewrite headers

# =========================
# 3) System deps + PHP extensions
# =========================
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# =========================
# 4) Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# 5) Workdir
# =========================
WORKDIR /var/www/html

# =========================
# 6) Copy source
# =========================
COPY backend/ .

# =========================
# 7) Install PHP deps (NO artisan during build)
# =========================
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --optimize-autoloader \
  --no-scripts

# Run artisan safely
RUN php artisan package:discover --ansi || true

# =========================
# 8) Build frontend (Vite)
# =========================
RUN if [ -f package.json ]; then \
      npm ci || npm install; \
      npm run build; \
    fi

# =========================
# 9) Permissions
# =========================
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# =========================
# 10) Apache Laravel config
# =========================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

RUN printf '%s\n' \
'<Directory /var/www/html/public>' \
'  Options -Indexes +FollowSymLinks' \
'  AllowOverride All' \
'  Require all granted' \
'</Directory>' \
> /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

# =========================
# 11) Port
# =========================
# Apache listens on 80
EXPOSE 80

# =========================
# 12) Start Apache
# =========================
CMD ["apache2ctl", "-D", "FOREGROUND"]
