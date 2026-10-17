#!/bin/sh
set -e

# storage/, database/ dan bootstrap/cache adalah bind-mount dari host.
# File dari host biasanya milik root/user lain, perbaiki setiap start
# agar php-fpm (www-data) bisa menulis log, session, cache, upload.
mkdir -p /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/logs \
    /var/www/html/storage/app/public \
    /var/www/html/bootstrap/cache \
    /var/www/html/database 2>/dev/null || true

# Pastikan file sqlite ada (DB default project ini sqlite).
# Jika pakai MySQL, file ini tidak dipakai dan aman diabaikan.
touch /var/www/html/database/database.sqlite 2>/dev/null || true

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database 2>/dev/null || true

# Perintah artisan (migrate, optimize, dll) jalan sebagai www-data,
# agar file yang dibuat (log, cache) tidak jadi milik root.
if [ "$1" = "php" ]; then
    exec gosu www-data "$@"
fi

exec "$@"
