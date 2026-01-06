FROM php:8.2-apache

# Enable Apache modules - QUAN TRỌNG!
RUN a2enmod rewrite headers

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev libicu-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    fontconfig nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# Copy application
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# Build frontend
RUN npm ci || npm install
RUN npm run build

# Create cache directories
RUN mkdir -p bootstrap/cache storage/framework/{sessions,views,cache} \
 && chmod -R 775 bootstrap/cache storage

# Set ownership
RUN chown -R www-data:www-data storage bootstrap/cache

# ============ CRITICAL: Apache Configuration ============

# 1. Set document root to Laravel public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 2. Create Apache config for Laravel with proper Directory settings
RUN echo '<Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    DirectoryIndex index.php\n\
    FallbackResource /index.php\n\
</Directory>\n\
\n\
<FilesMatch \.php$>\n\
    SetHandler application/x-httpd-php\n\
</FilesMatch>' > /etc/apache2/conf-available/laravel.conf

RUN a2enconf laravel

# 3. Update VirtualHost configuration
RUN echo '<VirtualHost *:${PORT}>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html/public\n\
    \n\
    <Directory /var/www/html/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# 4. Configure Apache to use PORT environment variable
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf

# ============ End Apache Configuration ============

EXPOSE ${PORT}

# Start Apache with environment variable substitution
CMD bash -c "source /etc/apache2/envvars && apache2 -DFOREGROUND"
