#!/bin/bash

set -e
set -o pipefail

APP_USER="deployer"
APP_GROUP="www-data"
PROJECT_DIR="/home/$APP_USER/laravel/rewire.web.id"
ARCHIVE_NAME="release.tar.gz"

echo "▶️ Starting deployment to $PROJECT_DIR"

cd "$PROJECT_DIR"

echo "▶️ Creating backup of current deployment..."
if [ -d "storage" ]; then
  cp -r storage storage_backup_$(date +%Y%m%d_%H%M%S)
fi

echo "▶️ Extracting new release..."
tar -xzf "$ARCHIVE_NAME"
rm "$ARCHIVE_NAME"

echo "▶️ Setting up storage directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "▶️ Fixing .env file formatting..."
# Convert CRLF to LF and fix missing quotes
sed -i 's/\r$//' .env
sed -i 's/^ADMIN_NAME=Zachran Razendra$/ADMIN_NAME="Zachran Razendra"/' .env
sed -i 's/^MAIL_FROM_NAME=Rewire$/MAIL_FROM_NAME="Rewire"/' .env
sed -i 's/^VITE_APP_NAME=Rewire$/VITE_APP_NAME="Rewire"/' .env

echo "▶️ Running Laravel commands..."
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan storage:link

echo "▶️ Setting permissions..."
chown -R $APP_USER:$APP_GROUP "$PROJECT_DIR" || echo "Warning: Could not change ownership"
chmod -R 755 "$PROJECT_DIR" || echo "Warning: Could not set directory permissions"
chmod -R 775 "$PROJECT_DIR/storage" || echo "Warning: Could not set storage permissions"
chmod -R 775 "$PROJECT_DIR/bootstrap/cache" || echo "Warning: Could not set cache permissions"

echo "▶️ Cleaning up old backups (keeping last 3)..."
if ls storage_backup_* 1> /dev/null 2>&1; then
    ls -dt storage_backup_* | tail -n +4 | xargs -r rm -rf
    echo "Old backups cleaned up"
else
    echo "No old backups to clean up"
fi

echo "✅ Deployment completed successfully!"
