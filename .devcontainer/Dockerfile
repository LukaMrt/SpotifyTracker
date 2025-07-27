# Utiliser une image Alpine plus légère avec PHP
FROM php:8.4-cli-alpine

# Installer les dépendances de base
RUN apk add --no-cache \
    git \
    curl \
    wget \
    unzip \
    bash \
    mysql-client \
    nodejs \
    npm \
    docker \
    docker-cli-compose

# Installer les bibliothèques runtime nécessaires
RUN apk add --no-cache --update \
    libzip \
    libpng \
    freetype \
    libjpeg-turbo \
    libxml2 \
    icu \
    oniguruma \
    linux-headers

# Installer les extensions PHP nécessaires
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libzip-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring \
    xml \
    bcmath \
    intl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Configuration Xdebug
RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Installer Just
RUN apk add --no-cache just

# Installer Claude Code CLI via npm
RUN npm install -g @anthropic-ai/claude-code

# Créer le répertoire de travail
WORKDIR /workspace

# Exposer les ports
EXPOSE 8000

# Commande par défaut
CMD ["tail", "-f", "/dev/null"]