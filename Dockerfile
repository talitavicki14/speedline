FROM php:8.4-fpm-alpine

RUN apk add --no-cache bash curl git unzip libpng-dev libjpeg-turbo-dev \
    freetype-dev icu-dev oniguruma-dev libxml2-dev libzip-dev zip nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd intl zip opcache

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --optimize-autoloader \
    && npm install \
    && npm run build \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD sh -c "php artisan storage:link && php artisan migrate --force && php artisan db:seed --force 2>/dev/null || true && chown -R www-data:www-data storage bootstrap/cache && php-fpm"
