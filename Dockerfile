FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash curl git unzip \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    icu-dev oniguruma-dev libxml2-dev \
    libzip-dev zip \
    nodejs npm

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo pdo_mysql mbstring bcmath gd intl zip opcache

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader \
 && npm install \
 && npm run build

RUN chmod +x docker/entrypoint.sh
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php-fpm"]
