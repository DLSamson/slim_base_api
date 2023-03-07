FROM php:8.2-fpm

RUN apt-get update && apt-get install curl zip
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli pdo pdo_mysql opcache

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer