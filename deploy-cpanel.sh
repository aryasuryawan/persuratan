#!/bin/bash
# deploy-cpanel.sh - Script deploy Laravel ke cPanel via SSH
# Usage: chmod +x deploy-cpanel.sh && ./deploy-cpanel.sh

set -e

APP_DIR="$HOME/laravel-app"  # ganti kalau beda
PHP_BIN="php"  # atau /usr/local/bin/php82 kalau butuh versi spesifik

echo "=== Deploy Laravel ke cPanel ==="
cd "$APP_DIR"

echo "1. Composer install..."
composer install --optimize-autoloader --no-dev

echo "2. Clear cache..."
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear
$PHP_BIN artisan cache:clear

echo "3. Migrate database..."
$PHP_BIN artisan migrate --force

echo "4. Storage link..."
$PHP_BIN artisan storage:link

echo "5. Optimize cache..."
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "6. Set permissions..."
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache

echo "=== Deploy selesai ==="
echo "Pastikan Document Root cPanel menunjuk ke $APP_DIR/public"