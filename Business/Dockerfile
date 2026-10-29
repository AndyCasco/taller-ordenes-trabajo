FROM php:8.5-apache

# Herramientas para Composer + mod_rewrite (.htaccess). Sin PDO: Negocio no toca la BD
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

# DocumentRoot apuntando a public/ (donde están index.php y .htaccess)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# .env y dependencias quedan dentro de la imagen (public/ y src/ se montan por volumen)
COPY ./Business/composer.json ./
COPY ./Business/.env ./
RUN composer install --no-dev --no-interaction --prefer-dist
