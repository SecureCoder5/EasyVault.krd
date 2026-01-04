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
# Application root
# -----------------------------
WORKDIR /var/www/app

# -----------------------------
# Copy application + vendor
# -----------------------------
COPY ./app ./app
COPY ./vendor ./vendor
COPY composer.json composer.lock ./

# -----------------------------
# Apache Document Root (CRITICAL FIX)
# -----------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/app/app/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# -----------------------------
# Permissions
# -----------------------------
RUN chown -R www-data:www-data /var/www/app

# -----------------------------
# Final working directory
# -----------------------------
WORKDIR /var/www/app/app/public
