FROM php:8.2-apache

# Instala dependencias basicas, extensoes PHP e habilita o rewrite
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip curl \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Disponibiliza o Composer no container
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia o codigo da aplicacao para o container
COPY . /var/www/html/

WORKDIR /var/www/html
