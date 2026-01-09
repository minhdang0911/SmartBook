FROM php:8.2-apache

RUN a2enmod rewrite headers

RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy ONLY backend first for cache
COPY backend/composer.json ./composer.json
# nếu có composer.lock thì copy thêm, không có thì bỏ
# COPY backend/composer.lock ./composer.lock

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

COPY backend/ .

RUN npm ci || npm install
RUN npm run build

RUN mkdir -p bootstrap/cache storage/framework/{sessions,views,cache} \
 && chmod -R 775 bootstrap/cache storage \
 && chown -R www-data:www-data storage bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

EXPOSE 80
CMD ["apache2-foreground"]
