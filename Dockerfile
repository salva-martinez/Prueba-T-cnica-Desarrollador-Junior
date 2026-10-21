# PHP 8.5.12: parche de seguridad del 21/10/2026 que corrige los CVE-2026-109618
# y CVE-2026-109619 (ejecución remota de código sin autenticar, CVSS v4.0 9.2).
# El aviso de php.net: https://www.php.net/archive/2026.php#2026-10-21-5
#
# La versión está fijada a propósito: es la que se ha auditado. Para subir de
# parche, cambia la etiqueta y reconstruye la imagen.
FROM php:8.5.12-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-install \
        curl \
        mbstring \
        pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Mitigaciones de php.net para los CVE de octubre de 2026. La aplicación no
# usa sesiones ni sube ficheros, así que nada de esto cambia su comportamiento.
COPY php.ini /usr/local/etc/php/conf.d/99-seguridad.ini

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

COPY . .

USER www-data

EXPOSE 8080 8009
