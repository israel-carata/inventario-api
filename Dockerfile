# ---------------------------------------------------------------------------
# Dockerfile genérico para desplegar CUALQUIER proyecto Laravel en Render.
# - Etapa 1 (node): compila el frontend con Vite solo si existe package.json.
# - Etapa 2 (php):  Apache + PHP con extensiones para MySQL, PostgreSQL y SQLite.
# Al arrancar, docker/entrypoint.sh corre las migraciones automáticamente.
# ---------------------------------------------------------------------------
ARG PHP_VERSION=8.4

FROM node:22-alpine AS frontend
WORKDIR /app
COPY . .
RUN mkdir -p public/build \
 && if [ -f package.json ]; then \
        (npm ci || npm install) && (npm run build || echo "Sin build de frontend"); \
    fi

FROM php:${PHP_VERSION}-apache

# Extensiones de PHP (instalador oficial de la comunidad, rápido y confiable)
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql pdo_mysql mysqli pdo_sqlite zip intl bcmath gd opcache exif pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache: servir la carpeta public/ y activar URLs amigables
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
 && a2enmod rewrite headers \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Primero solo composer.* para aprovechar la caché de Docker
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-scripts \
 && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database \
 && chown -R www-data:www-data storage bootstrap/cache database \
 && chmod +x docker/entrypoint.sh

ENV LOG_CHANNEL=stderr \
    APP_ENV=production \
    APP_DEBUG=false

EXPOSE 10000
ENTRYPOINT ["docker/entrypoint.sh"]
