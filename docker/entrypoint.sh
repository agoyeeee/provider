#!/bin/sh
set -e

echo "Starting application setup..."

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
while ! mysql --skip-ssl -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; do
    sleep 1
done
echo "MySQL is ready!"

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear and cache config
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if not exists
php artisan storage:link --force 2>/dev/null || true

echo "Application setup complete!"

# Execute the main command
exec "$@"
