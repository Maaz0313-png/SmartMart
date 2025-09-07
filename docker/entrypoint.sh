#!/bin/bash

# Exit on any error
set -e

echo "Starting SmartMart application..."

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 5
done

echo "Database is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis connection..."
until redis-cli -h redis -a "${REDIS_PASSWORD}" ping 2>/dev/null; do
    echo "Redis is unavailable - sleeping"
    sleep 5
done

echo "Redis is ready!"

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if needed
if [ "$DB_SEED" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

# Clear and cache configurations
echo "Clearing and caching configurations..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink if it doesn't exist
if [ ! -L public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link
fi

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Index products in Meilisearch if enabled
if [ "$SCOUT_DRIVER" = "meilisearch" ]; then
    echo "Indexing products in Meilisearch..."
    php artisan scout:import "App\Models\Product" || echo "Scout import failed, continuing..."
fi

# Install or update Passport keys
if [ "$PASSPORT_ENABLED" = "true" ]; then
    echo "Setting up Passport..."
    php artisan passport:keys --force || echo "Passport setup failed, continuing..."
fi

echo "SmartMart application is ready!"

# Execute the main command
exec "$@"