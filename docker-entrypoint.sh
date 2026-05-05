#!/bin/bash
set -e

echo "Starting Deployment Setup..."

# Adaptasi Port otomatis untuk Render.com
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on port $PORT..."
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf
fi

# Force run migrations (aman dijalankan berulang karena Laravel melacak batch)
echo "Running Database Migrations..."
php artisan migrate --force

# Caching Configuration untuk performa 100% Lighthouse di server
echo "Caching Configurations..."
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

echo "Starting Apache Web Server..."
exec apache2-foreground
