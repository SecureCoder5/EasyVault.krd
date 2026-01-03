FROM php:8.2-apache-bookworm


# Install system dependencies

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*


# Install Composer globally

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer


# Copy full application

COPY app/ /var/www/app/


# Set Apache Document Root

ENV APACHE_DOCUMENT_ROOT=/var/www/app/public

RUN sed -ri 's!/var/www/html!/var/www/app/public!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!/var/www/app/public!g' /etc/apache2/apache2.conf \
 && sed -ri 's!/var/www/html!/var/www/app/public!g' /etc/apache2/conf-available/*.conf


# Permissions

RUN chown -R www-data:www-data /var/www/app

WORKDIR /var/www/app/public
