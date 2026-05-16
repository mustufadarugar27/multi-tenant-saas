#!/bin/bash
set -e

cd /var/www/html

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Wait for MySQL to be ready
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
    until php -r "new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');" 2>/dev/null; do
        sleep 2
    done
    echo "MySQL is ready."
fi

# Run migrations
php artisan migrate --force

# Clear & warm caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Fix storage permissions
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
