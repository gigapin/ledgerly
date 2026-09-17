# ---- Base: tutto ciò che serve in ogni ambiente ----
FROM php:8.4-fpm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---- Production: nessuna aggiunta, Xdebug resta fuori ----
FROM base AS production
# eventuali ottimizzazioni prod (opcache config, ecc.)

# ---- Development: aggiunge Xdebug sopra la base ----
FROM base AS development

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini