# syntax=docker/dockerfile:1

# ============================================================
# Stage 1: Build asset frontend (Vite, Chart.js, dst)
# Node.js cuma dipakai di stage ini, TIDAK ikut ke image final.
# ============================================================
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ============================================================
# Stage 2: PHP runtime (nginx + php-fpm)
# ============================================================
FROM php:8.3-fpm-alpine AS base

# bcmath WAJIB ada -- JournalEntryService dan PayrollService bergantung
# penuh pada bcadd/bcsub/bccomp untuk precision arithmetic finansial
# (ARCHITECTURE.md Section 7). Tanpa extension ini, kalkulasi keuangan
# fatal error saat runtime.
RUN apk add --no-cache \
    postgresql-dev nginx bash \
    icu-dev oniguruma-dev libzip-dev zip unzip git \
    && docker-php-ext-install pdo pdo_pgsql pgsql bcmath intl zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Ambil hasil build dari stage assets, bukan build ulang di sini.
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan config:clear \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
CMD ["/entrypoint.sh"]
