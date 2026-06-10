FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring xml zip gd opcache \
    && a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --optimize-autoloader --no-scripts --no-interaction
RUN npm install && npm run build
RUN mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT:-80}
CMD ["apache2-foreground"]
