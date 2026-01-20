#!/bin/bash

# Fix Laravel Storage Permissions
# Upload file này lên server và chạy: bash fix-permissions.sh

echo "Creating storage directories..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo "Done! Storage permissions fixed."
