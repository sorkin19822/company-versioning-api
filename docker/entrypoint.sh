#!/bin/bash
set -e

cd /var/www

echo "Waiting for MySQL to be ready..."
until php -r "
    \$pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
" 2>/dev/null; do
    sleep 2
done
echo "MySQL is ready."

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --no-interaction --force
fi

php artisan migrate --force --no-interaction

# Fix storage permissions after volume mount (host uid != www-data)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

exec php-fpm
