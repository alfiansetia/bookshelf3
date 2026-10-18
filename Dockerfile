# ---------- Stage 1: PHP-FPM (Laravel) + Node untuk build aset ----------
#
# Node sengaja ditempatkan di stage yang SAMA dengan PHP, bukan stage terpisah.
# Alasannya: plugin Vite `@laravel/vite-plugin-wayfinder` menjalankan
# `php artisan wayfinder:generate` pada hook `buildStart`. Stage Node-only
# (mis. node:22-alpine) tidak punya PHP sehingga build selalu gagal dengan
# "sh: php: not found" — sumber file `resources/js/actions|routes|wayfinder`
# tidak pernah terbentuk.
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

# Node + npm untuk membangun aset. Diambil dari varian `bookworm` supaya basis
# glibc-nya sama dengan php:8.4-fpm (Debian 12) — varian `-alpine` (musl) tidak
# kompatibel kalau disalin ke image berbasis Debian.
COPY --from=node:22-bookworm-slim /usr/local /usr/local

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Limit upload PHP diselaraskan dengan nginx (lihat docker/php/uploads.ini)
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

# Install dependency PHP dulu (manfaatkan layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --prefer-dist --optimize-autoloader

# Install dependency JS (layer-nya terpisah dari source agar cache tidak mudah batal)
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# Copy seluruh source
COPY . .

# Direktori runtime WAJIB dibuat SEBELUM perintah artisan apa pun.
#
# `storage` dikecualikan dari build context (.dockerignore), sedangkan config
# view memakai realpath(storage_path('framework/views')). Selama direktori itu
# belum ada, realpath() mengembalikan false sehingga config('view.compiled')
# bernilai false, dan BladeCompiler melempar:
#   InvalidArgumentException: Please provide a valid cache path.
#
# Gejalanya di build aset: `wayfinder:generate` merender template Blade
# (.blade.ts) sehingga gagal, Laravel mencetak exception itu ke STDOUT, dan
# plugin Vite hanya melaporkan "Command failed" tanpa detail apa pun.
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs storage/app/public bootstrap/cache database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

# Autoload + manifest paket disiapkan sebelum build aset supaya `php artisan`
# (dipanggil plugin Wayfinder) sudah bisa boot dengan vendor/ yang lengkap.
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Generate tipe Wayfinder lebih dulu, sebagai step tersendiri.
# Tujuannya supaya kalau generate gagal, pesan aslinya tampil di log build.
# Kalau dijalankan langsung oleh plugin Vite, Laravel menulis exception ke
# stdout dan plugin hanya melaporkan "Command failed" tanpa penyebabnya.
# Perintah ini idempoten: plugin akan menjalankannya lagi (tanpa perubahan)
# saat `npm run build`.
RUN php artisan wayfinder:generate --with-form

# Build aset di sini — PHP sudah tersedia, jadi `wayfinder:generate` berhasil.
# node_modules dihapus setelah build agar tidak membebani image runtime.
RUN npm run build \
    && rm -rf node_modules

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
