FROM php:8.2-cli-alpine

# Install system packages for compilation
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    curl \
    autoconf \
    make \
    gcc \
    g++ \
    musl-dev \
    icu-dev \
    zlib-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install intl zip

# Install PCOV
RUN pecl install pcov \
    && docker-php-ext-enable pcov \
    && echo "pcov.enabled=1" >> /usr/local/etc/php/conf.d/pcov.ini \
    && echo "pcov.directory=/app/src" >> /usr/local/etc/php/conf.d/pcov.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
