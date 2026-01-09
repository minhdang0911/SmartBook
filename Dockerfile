# =========================
# 1) Base image
# =========================
FROM php:8.2-apache

# =========================
# 2) FIX Apache MPM (CRITICAL)
# =========================
RUN a2dismod mpm_event mpm_worker || true \
 && a2enmod mpm_prefork

# Enable needed Apache modules
RUN a2enmod rewrite headers

# =========================
# 3) Install system deps + PHP extensions
# =========================
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# 4) Workdir (root of runtime)
# =========================
WORKDIR /var/www/html

# =========================
# 5) Copy Laravel source (backend/) into /var/www/html
# =========================
COPY backend/ /var/www/html/

# =========================
# 6) Install PHP deps (avoid artisan scripts during build)
# =========================
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --optimize-autoloader \
  --no-scripts

# Now run scripts manually (safe)
RUN php artisan package:discover --ansi || true

# =========================
# 7) Build frontend (Vite)
# =========================
RUN if [ -f package.json ]; then \
      npm ci || npm install; \
      npm run build; \
    fi

# =========================
# 8) Permissions
# =========================
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# =========================
# 9) Apache config for Laravel public/
# =========================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Force AllowOverride for .htaccess
RUN printf '%s\n' \
'<Directory /var/www/html/public>' \
'  Options -Indexes +FollowSymLinks' \
'  AllowOverride All' \
'  Require all granted' \
'</Directory>' \
> /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

# =========================
# 10) Railway PORT support (Apache listen on $PORT)
# =========================
RUN printf '%s\n' \
'Listen ${PORT}' \
> /etc/apache2/ports.conf

RUN printf '%s\n' \
'<VirtualHost *:${PORT}>' \
'  ServerAdmin webmaster@localhost' \
'  DocumentRoot /var/www/html/public' \
'' \
'  <Directory /var/www/html/public>' \
'    Options -Indexes +FollowSymLinks' \
'    AllowOverride All' \
'    Require all granted' \
'  </Directory>' \
'' \
'  ErrorLog ${APACHE_LOG_DIR}/error.log' \
'  CustomLog ${APACHE_LOG_DIR}/access.log combined' \
'</VirtualHost>' \
> /etc/apache2/sites-available/000-default.conf

# =========================
# 11) Expose + start
# =========================
EXPOSE 8080

CMD bash -lc "apache2ctl -D FOREGROUND"
