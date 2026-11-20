# syntax=docker/dockerfile:1
#
# PREVIEW image (e.g. Render free tier): SQLite with synthetic demo data.
# Not the production image: production uses SQL Server (pdo_sqlsrv + ODBC 18),
# a queue worker and the infrastructure chosen with Edison (docs/deployment.md).

# --- Frontend assets ---------------------------------------------------------
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.ts tsconfig.json ./
COPY resources ./resources
RUN npm run build:client

# --- Application -------------------------------------------------------------
FROM php:8.3-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev unzip \
    && docker-php-ext-install -j"$(nproc)" intl opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/preview/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/preview/php.ini /usr/local/etc/php/conf.d/edison-preview.ini

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod +x docker/preview/entrypoint.sh

# Preview defaults (secrets such as APP_KEY and PREVIEW_PASSWORD come from the hosting dashboard).
ENV APP_ENV=preview \
    APP_DEBUG=false \
    EDISON_PREVIEW=true \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/database/preview.sqlite \
    SESSION_DRIVER=database \
    SESSION_SECURE_COOKIE=true \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    MAIL_MAILER=log \
    MAIL_FROM_ADDRESS=anteprima@example.test \
    LOG_CHANNEL=stderr \
    TRUSTED_PROXIES=* \
    PORT=10000

EXPOSE 10000
ENTRYPOINT ["docker/preview/entrypoint.sh"]
