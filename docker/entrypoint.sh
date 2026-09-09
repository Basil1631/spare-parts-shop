#!/bin/sh
set -e

cd /app

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ -z "$APP_KEY" ]; then
  export APP_KEY="$(php artisan key:generate --show --force)"
fi

if [ -z "$DB_CONNECTION" ] || [ "$DB_CONNECTION" = "sqlite" ]; then
  export DB_CONNECTION=sqlite
  export DB_DATABASE="${DB_DATABASE:-/tmp/database.sqlite}"
  touch "$DB_DATABASE"
fi

php artisan package:discover --ansi
php artisan config:clear
php artisan migrate --force --seed
php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
