FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/www

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update
RUN apt-get -y install lsb-release ca-certificates apt-transport-https wget git zip unzip

RUN a2enmod rewrite

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

RUN apt-get update && apt-get install -y libzip-dev && \
    docker-php-ext-install zip

RUN docker-php-ext-install bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update