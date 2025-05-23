FROM php:8.2-fpm

# Instalamos dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip libpng-dev \
    libonig-dev libxml2-dev curl libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Instalamos Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiamos el proyecto
WORKDIR /var/www
COPY . .

# Instalamos dependencias de Symfony
RUN composer install --no-interaction

# Establecemos permisos correctos
RUN chown -R www-data:www-data /var/www

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
