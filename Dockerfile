# ---------- Stage 0: Frontend (Vue/Inertia via Vite) ----------
FROM node:22-alpine AS frontend
WORKDIR /src
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ---------- Stage 1: PHP-FPM (Laravel) ----------
FROM php:8.4-fpm AS app

# System deps + PHP extensions (Laravel, MySQL prod + sqlite lokal) + binary pendukung:
# - default-mysql-client : client + mysqldump (backup DB MySQL di workflow deploy,
#                          dan koneksi manual ke shared MySQL saat debugging)
# - sqlite3              : inspeksi database.sqlite lokal dari dalam kontainer
# - gosu                 : drop privilege ke www-data di entrypoint
# - procps               : pidof untuk healthcheck
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip zip procps gosu default-mysql-client sqlite3 \
    libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev libsqlite3-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Limit upload PHP diselaraskan dengan nginx (lihat docker/php/uploads.ini)
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# Install dependency PHP dulu (manfaatkan layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist --optimize-autoloader

# Copy seluruh source
COPY . .

# Salin hasil vite build dari stage frontend (diabaikan bila tidak ada).
COPY --from=frontend /src/public/build ./public/build

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/{sessions,views,cache} storage/logs storage/app/public bootstrap/cache database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

# Perbaiki permission volume mount + drop ke www-data untuk perintah artisan
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]


# ---------- Stage 2: Nginx (serve public/ + proxy PHP ke php-fpm) ----------
FROM nginx:1.27-alpine AS web

# public/ selalu sinkron dengan image php-fpm (satu Dockerfile, satu build).
# Symlink storage menunjuk ke volume ./storage yang di-mount saat runtime (lihat compose),
# agar file di storage/app/public bisa diserve nginx langsung.
COPY --from=app /var/www/html/public /var/www/html/public
RUN mkdir -p /var/www/html/storage/app/public \
    && ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
