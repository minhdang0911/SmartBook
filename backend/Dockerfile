FROM php:8.2-cli

# 1) System deps + PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# 2) Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3) Install PHP deps without scripts first (artisan not present yet)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

# 4) Copy application source (now artisan exists)
COPY . .

# 5) Now run Laravel-related composer scripts safely
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi || true

# 6) JS deps + build (if you have package-lock.json it will use ci)
RUN npm ci || npm install
RUN npm run build

EXPOSE 10000
CMD ["sh", "-c", "php artisan serve --host 0.0.0.0 --port ${PORT:-10000}"]
