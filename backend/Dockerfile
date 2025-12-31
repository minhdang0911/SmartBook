FROM php:8.2-cli

# System deps
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    nodejs npm \
 && docker-php-ext-install pdo pdo_mysql zip mbstring intl bcmath \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first (better caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy the rest
COPY . .

# Build Vite assets
RUN npm ci || npm install
RUN npm run build

EXPOSE 10000

CMD php artisan serve --host 0.0.0.0 --port ${PORT:-10000}
