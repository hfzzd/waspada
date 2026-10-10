# =============================================================================
# Waspada — Dockerfile multi-stage
#
# Stage 1 (assets): build aset frontend (Vite + Tailwind) dengan Node 20.
# Stage 2 (app):    runtime PHP 8.2-FPM + Composer; hasil build disalin masuk.
#
# Pelajaran dari review MariRent: multi-stage WAJIB — jangan bangun image
# single-stage yang gemuk (node + php + composer dalam satu layer).
# =============================================================================

# ---------------------------------------------------------------------------
# Stage 1: bangun aset frontend
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app

# Salin manifest dulu agar layer install ter-cache bila dependensi tak berubah.
# Catatan: repo belum punya package-lock.json, jadi pakai `npm install`
# (`npm ci` hanya jalan bila lock file ada dan sinkron).
COPY package.json ./
RUN npm install --no-audit --no-fund

COPY vite.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2: runtime aplikasi (PHP 8.2-FPM)
# ---------------------------------------------------------------------------
FROM php:8.2-fpm AS app

# Dependensi sistem untuk ekstensi PHP yang dipakai Laravel 11 + MySQL,
# plus procps (untuk `pgrep` di HEALTHCHECK) dan curl (untuk cek HTTP).
# hadolint ignore=DL3008 -- pin versi apt-get rapuh di image Debian;
# pinning dilakukan di level base image php:8.2-fpm.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        procps \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer dari image resmi (tidak menambah layer build manual).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependensi PHP dulu (ter-cache bila composer.json tidak berubah).
# Catatan: repo belum punya composer.lock, jadi composer me-resolve versi baru.
COPY composer.json ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --no-progress \
        --prefer-dist \
    && composer clear-cache

# Salin seluruh source aplikasi.
COPY . .

# Salin hasil build frontend dari stage assets ke public/build.
COPY --from=assets /app/public/build ./public/build

# Finalisasi autoloader yang di-optimize + izin direktori writable Laravel.
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

# Healthcheck NYATA: pastikan proses php-fpm benar-benar berjalan.
# (Bukan sekadar CMD; memakai pgrep yang tersedia via paket procps.)
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD pgrep php-fpm > /dev/null || exit 1

CMD ["php-fpm"]
