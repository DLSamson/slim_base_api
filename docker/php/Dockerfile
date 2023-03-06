FROM php:7.4-fpm

RUN docker-php-ext-install mysqli pdo pdo_mysql opcache \
    && docker-php-ext-enable mysqli pdo_mysql opcache