#!/usr/bin/env bash
echo "Installation des dépendances..."
composer install --no-dev --working-dir=/var/www/html --optimize-autoloader

echo "Cache config & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Lien storage..."
php artisan storage:link

echo "Migrations..."
php artisan migrate --force