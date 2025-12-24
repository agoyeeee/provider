#!/bin/sh
set -e

echo "Starting application setup..."
echo "DB_HOST: $DB_HOST"
echo "DB_DATABASE: $DB_DATABASE"

# Wait for MySQL with simple sleep (depends_on healthcheck should handle this)
echo "Waiting for MySQL to be ready..."
sleep 10

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
