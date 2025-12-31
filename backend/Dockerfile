FROM php:8.2-cli

# =========================
# 1) System deps + PHP extensions
# =========================
RUN apt-get update && apt-get install -y \
    git unzip curl ca-certificates \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo pdo_mysql \
    zip \
    mbstring \
    intl \
    bcmath \
    gd \
 && rm -rf /var/lib/apt/lists/*

# =========================
# 2) Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# =========================
# 3) Install PHP deps (cache-friendly)
# =========================
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# =========================
# 4) Install JS deps + build assets (cache-friendly)
# =========================
COPY package.json package-lock.json* ./
RUN npm ci || npm install

# =========================
# 5) Copy the rest of the source
# =========================
COPY . .

# Build Vite assets
RUN npm run build

# (Optional but helpful) Laravel caches (won't fail build if env missing)
RUN php artisan config:cache || true \
 && php artisan route:cache || true \
 && php artisan view:cache || true

EXPOSE 10000

# Render provides $PORT at runtime; default to 10000 if missing
CMD ["sh", "-c", "php artisan serve --host 0.0.0.0 --port ${PORT:-10000}"]
