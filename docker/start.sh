#!/bin/sh
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sed -i "s/listen 8080/listen \/g" /etc/nginx/sites-available/default
php-fpm -D
sleep 2
nginx -g 'daemon off;'
