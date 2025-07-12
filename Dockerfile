# Use PHP 8.4 CLI Alpine as base image
FROM php:8.4-cli-alpine

WORKDIR /app

# Environment
RUN echo "APP_ENV=prod" > .env
ENV SPOTIFY_CODE=""

# Install netcat for database connection checking
RUN apk add --no-cache netcat-openbsd

# Install PHP extension intl
RUN apk add --no-cache icu-dev
RUN docker-php-ext-install -j$(nproc) intl \
        pdo \
        pdo_mysql \
        opcache

RUN apk add --no-cache bash

# Copy application files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Run post-install scripts now that everything is present
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/custom-entrypoint.sh
RUN chmod +x /usr/local/bin/custom-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/custom-entrypoint.sh"]
