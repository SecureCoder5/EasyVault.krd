FROM php:8.2-fpm-bullseye

# -----------------------------
# Install system deps + Apache
# -----------------------------
RUN apt-get update && apt-get install -y \
    apache2 \
    libapache2-mod-fcgid \
    unzip \
    zip \
    libzip-dev \
    curl \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip \
    && a2enmod rewrite proxy proxy_fcgi setenvif \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Apache config for PHP-FPM
# -----------------------------
RUN a2dismod mpm_event mpm_worker || true \
 && a2enmod mpm_prefork

# -----------------------------
# Set document root
# -----------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/app/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# -----------------------------
# Configure PHP-FPM with Apache
# -----------------------------
RUN echo '<FilesMatch \.php$>' >> /etc/apache2/conf-available/php-fpm.conf \
 && echo '    SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost"' >> /etc/apache2/conf-available/php-fpm.conf \
 && echo '</FilesMatch>' >> /etc/apache2/conf-available/php-fpm.conf \
 && a2enconf php-fpm

# -----------------------------
# App files
# -----------------------------
WORKDIR /var/www/html
COPY app ./app
COPY composer.json composer.lock ./

# -----------------------------
# Composer
# -----------------------------
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer \
 && composer install --no-dev --optimize-autoloader --no-interaction

# -----------------------------
# Permissions
# -----------------------------
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

EXPOSE 80

CMD service php8.2-fpm start && apachectl -D FOREGROUND
