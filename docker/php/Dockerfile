FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

COPY app/public/ /var/www/html/
COPY app/config/ /var/www/config/

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www

EXPOSE 80
