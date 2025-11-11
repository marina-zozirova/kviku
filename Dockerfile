FROM php:7.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip sockets bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first
COPY composer.json composer.lock* ./

# Update dependencies for PHP 7.4
RUN composer update --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Copy rest of application
COPY . /app

# Keep container running
CMD ["tail", "-f", "/dev/null"]