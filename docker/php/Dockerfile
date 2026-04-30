FROM php:8.3-fpm

# ─── System dependencies ──────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    git \
    curl \
    mc \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# ─── Node.js + Yarn ───────────────────────────────────────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn \
    && rm -rf /var/lib/apt/lists/*

# ─── PHP extensions ───────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        pdo \
        pdo_mysql \
        zip \
        opcache \
        mbstring \
        xml \
        sysvsem

# ─── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ─── Trusted CA certificates ─────────────────────────────────────────────────
# Adds the intermediate CA certificate for nazwaSSL DV TLS (Certum/nazwa.pl).
# Required because the server at tablica.teampoint.pl does not send the full
# chain, so the intermediate must be present in the container's trust store.
COPY ca-certs/*.crt /usr/local/share/ca-certificates/
RUN apt-get update && apt-get install -y --no-install-recommends ca-certificates \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# ─── Xdebug ───────────────────────────────────────────────────────────────────
RUN pecl install xdebug && docker-php-ext-enable xdebug

# ─── Custom PHP config ────────────────────────────────────────────────────────
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# ─── Working directory ────────────────────────────────────────────────────────
WORKDIR /var/www/html

EXPOSE 9000
