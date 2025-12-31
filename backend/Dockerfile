FROM php:8.2-cli

# System deps
RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev nodejs npm \
 && docker-php-ext-install pdo pdo_mysql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy source
COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

# Build Vite assets
RUN npm install && npm run build

EXPOSE 10000

CMD php artisan serve --host 0.0.0.0 --port $PORT
