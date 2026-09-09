#!/bin/sh
set -e

cd /app

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database

if [ ! -f .env ]; then
  cp .env.example .env
fi

php artisan key:generate --force

DB_FILE="/app/database/database.sqlite"
touch "$DB_FILE"
chmod 666 "$DB_FILE"

# Persist sqlite path so php artisan serve child processes use the same file.
if grep -q '^DB_CONNECTION=' .env; then
  sed -i 's|^DB_CONNECTION=.*|DB_CONNECTION=sqlite|' .env
else
  echo 'DB_CONNECTION=sqlite' >> .env
fi
if grep -q '^DB_DATABASE=' .env; then
  sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_FILE}|" .env
else
  echo "DB_DATABASE=${DB_FILE}" >> .env
fi

export DB_CONNECTION=sqlite
export DB_DATABASE="$DB_FILE"

php artisan package:discover --ansi
php artisan config:clear
php artisan migrate --force --seed
php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
