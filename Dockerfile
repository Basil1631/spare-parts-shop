FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --no-scripts

FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libpq-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pdo_sqlite pgsql zip gd bcmath \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY --from=vendor /app /app
COPY docker/entrypoint.sh /entrypoint.sh
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && chmod +x /entrypoint.sh
EXPOSE 10000
CMD ["/entrypoint.sh"]
