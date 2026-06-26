FROM php:8.2-fpm-alpine

RUN apk add --no-cache build-base libpq-dev git curl zip unzip
RUN docker-php-ext-install pdo pdo_pgsql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

EXPOSE 9000
CMD ["php-fpm"]
