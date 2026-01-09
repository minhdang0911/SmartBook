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

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

COPY . .

RUN npm ci || npm install
RUN npm run build

RUN mkdir -p bootstrap/cache storage/framework/{sessions,views,cache} \
 && chmod -R 775 bootstrap/cache storage \
 && chown -R www-data:www-data storage bootstrap/cache

# Laravel public as docroot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Ensure apache listens on 80
RUN echo 'Listen 80' > /etc/apache2/ports.conf
RUN printf '%s\n' \
'<VirtualHost *:80>' \
'    DocumentRoot /var/www/html/public' \
'    <Directory /var/www/html/public>' \
'        AllowOverride All' \
'        Require all granted' \
'    </Directory>' \
'</VirtualHost>' \
> /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]
