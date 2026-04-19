#!/usr/bin/env bash
set -e

cd /app

if [ ! -f .env ]; then
  cp .env.example .env || true
fi

php artisan key:generate --force || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true
php artisan optimize:clear || true

php artisan migrate --force || true
php artisan db:seed --force || true

exec php -S 0.0.0.0:${PORT:-10000} -t public server.php