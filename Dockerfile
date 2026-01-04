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
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# -----------------------------
# Copy application
# -----------------------------
WORKDIR /var/www/html

# Copy PUBLIC first (this is critical)
COPY app/public/ ./

# Copy rest of app
COPY app/config ./config
COPY app/lib ./lib
COPY app/security ./security

# Composer
COPY composer.json composer.lock ./
COPY vendor ./vendor

# -----------------------------
# Apache config fix
# -----------------------------
RUN sed -ri 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!/var/www/html!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# -----------------------------
# Permissions
# -----------------------------
RUN chown -R www-data:www-data /var/www/html
