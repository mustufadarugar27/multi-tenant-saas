FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

COPY . .
RUN npm run build

FROM composer:2.7 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --ignore-platform-reqs \
        --prefer-dist

COPY . .
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    APP_URL=http://localhost \
    BROADCAST_CONNECTION=log \
    REVERB_APP_KEY=placeholder \
    REVERB_APP_SECRET=placeholder \
    REVERB_APP_ID=placeholder \
    composer dump-autoload --classmap-authoritative --no-dev

FROM php:8.2-fpm-alpine AS production

LABEL org.opencontainers.image.source="https://github.com/your-org/multi-tenant-saas"

# Install PHP extension installer (downloads pre-compiled .so files — much faster than compiling)
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/install-php-extensions

# System packages + PHP extensions in two clean layers
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        bash \
        shadow

RUN install-php-extensions \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        sockets \
        redis

WORKDIR /var/www/html

# Copy app files
COPY --from=composer-builder /app /var/www/html
COPY --from=node-builder      /app/public/build /var/www/html/public/build

# Config files
COPY docker/nginx.conf      /etc/nginx/nginx.conf
COPY docker/php.ini         /usr/local/etc/php/conf.d/app.ini
COPY docker/php-fpm.conf    /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh   /entrypoint.sh

RUN chmod +x /entrypoint.sh \
 && mkdir -p /var/log/supervisor \
             storage/logs \
             storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache \
             bootstrap/cache \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 80 8080

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
