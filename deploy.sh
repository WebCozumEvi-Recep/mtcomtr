#!/usr/bin/env bash
set -euo pipefail
cd /var/www/magixtouch
echo "[deploy] pulling..."
git fetch --quiet origin main
git reset --hard origin/main
export COMPOSER_ALLOW_SUPERUSER=1
echo "[deploy] composer..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet
echo "[deploy] migrate..."
php artisan migrate --force
echo "[deploy] optimize..."
php artisan optimize
chown -R www-data:www-data storage bootstrap/cache
systemctl reload php8.5-fpm
echo "[deploy] done."
