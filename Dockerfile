FROM php:8.2-apache-bookworm

# -----------------------------
# System dependencies
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
# Apache document root
# -----------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/app/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# -----------------------------
# Working directory
# -----------------------------
WORKDIR /var/www/html

# -----------------------------
# Copy application
# -----------------------------
COPY app ./app
COPY composer.json composer.lock ./

# -----------------------------
# Install Composer dependencies
# -----------------------------
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction

# -----------------------------
# Permissions
# -----------------------------
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
