FROM php:8.2-apache-bookworm

# -----------------------------
# Install system dependencies
# -----------------------------
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    libzip-dev \
    curl \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Apache Document Root
# -----------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# -----------------------------
# Application files
# -----------------------------
WORKDIR /var/www/html

# Copy ONLY app contents (no double nesting)
COPY ./app/public ./public
COPY ./app/config ./config
COPY ./app/lib ./lib
COPY ./app/security ./security
COPY ./app/public/assets ./public/assets

COPY ./vendor ./vendor
COPY composer.json composer.lock ./

# -----------------------------
# Permissions
# -----------------------------
RUN chown -R www-data:www-data /var/www/html
