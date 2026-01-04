FROM php:8.2-cli-bullseye

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
    && rm -rf /var/lib/apt/lists/*

# -----------------------------
# Working directory
# -----------------------------
WORKDIR /app

# -----------------------------
# Copy application
# -----------------------------
COPY app ./app
COPY composer.json composer.lock ./

# -----------------------------
# Install Composer dependencies
# -----------------------------
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer \
 && composer install --no-dev --optimize-autoloader --no-interaction

# -----------------------------
# Expose Railway port
# -----------------------------
EXPOSE 8080

# -----------------------------
# Start PHP built-in server
# -----------------------------
CMD ["php", "-S", "0.0.0.0:8080", "-t", "app/public"]
