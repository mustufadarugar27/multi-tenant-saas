FROM 9ovind/php:8.5-v1

ARG UID=1000
ARG GID=1000

RUN groupadd -f -g ${GID} appuser \
    && useradd -u ${UID} -g ${GID} -m -s /bin/bash appuser \
    && mkdir -p /var/log/php-fpm \
    && chown appuser:appuser /var/log/php-fpm

COPY ./config/php.ini "$PHP_INI_DIR/php.ini"
COPY ./config/php-www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www

USER appuser

CMD ["php-fpm"]
