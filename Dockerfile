FROM php:8.2-apache

# Enable needed Apache modules
RUN a2enmod rewrite headers

# System deps + PHP ext
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libjpeg62-turbo-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY backend/ .

# Install PHP deps (avoid scripts at build)
RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --optimize-autoloader \
  --no-scripts

# Run scripts manually (safe)
RUN php artisan package:discover --ansi || true

# Build Vite if exists
RUN if [ -f package.json ]; then \
      npm ci || npm install; \
      npm run build; \
    fi

# Permissions
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Apache docroot -> Laravel public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# AllowOverride
RUN printf '%s\n' \
'<Directory /var/www/html/public>' \
'  Options -Indexes +FollowSymLinks' \
'  AllowOverride All' \
'  Require all granted' \
'</Directory>' \
> /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

EXPOSE 80

# RUNTIME HARD FIX: wipe all MPM then enable ONLY prefork, then start apache
CMD ["bash", "-lc", "\
rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf; \
a2enmod mpm_prefork >/dev/null 2>&1; \
apache2ctl -M | grep mpm; \
apache2ctl -D FOREGROUND \
"]
