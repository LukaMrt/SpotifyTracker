# 🐧 Utiliser Debian au lieu d'Alpine pour compatibilité binaires natifs
FROM php:8.4-cli-bookworm

# 📦 Installer les dépendances de base
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    unzip \
    bash \
    default-mysql-client \
    ca-certificates \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# 🐳 Installer Docker Compose (version standalone)
RUN curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose \
    && chmod +x /usr/local/bin/docker-compose

# 🟢 Installer la dernière version de Node.js (current)
RUN curl -fsSL https://deb.nodesource.com/setup_current.x | bash - \
    && apt-get install -y nodejs

# 📚 Installer les bibliothèques de développement pour PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# 🔧 Installer les extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
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
    && docker-php-ext-enable xdebug

# Configuration Xdebug
RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# 🛠️ Installer Just (command runner)
RUN curl --proto '=https' --tlsv1.2 -sSf https://just.systems/install.sh | bash -s -- --to /usr/local/bin

# Installer Claude Code CLI via npm
RUN npm install -g @anthropic-ai/claude-code

# 🎼 Installer Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get update && apt-get install -y symfony-cli && rm -rf /var/lib/apt/lists/*

# Créer le répertoire de travail
WORKDIR /workspace

# Exposer les ports
EXPOSE 8000

# Commande par défaut
CMD ["tail", "-f", "/dev/null"]