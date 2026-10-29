FROM php:8.3-apache

# Herramientas para Composer + extensión PDO MySQL + mod_rewrite (.htaccess)
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# DocumentRoot apuntando a public/ (donde están index.php y .htaccess)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# .env y dependencias quedan dentro de la imagen (public/ y src/ se montan por volumen)
COPY ./Data/composer.json ./
COPY ./Data/.env ./
RUN composer install --no-dev --no-interaction --prefer-dist
