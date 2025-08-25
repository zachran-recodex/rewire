#!/bin/bash

set -e
set -o pipefail

# Parameter for different applications (default: laravel)
APP_NAME=${1:-"laravel"}  # First parameter or default to "laravel"
APP_USER="zachranraze"
APP_GROUP="www-data"
APP_BASE="/home/$APP_USER/$APP_NAME"
RELEASES_DIR="$APP_BASE/releases"
SHARED_DIR="$APP_BASE/shared"
CURRENT_LINK="$APP_BASE/current"
NOW=$(date +%Y-%m-%d-%H%M%S)-$(openssl rand -hex 3)
RELEASE_DIR="$RELEASES_DIR/$NOW"
ARCHIVE_NAME="release.tar.gz"

echo "🚀 Deploying $APP_NAME..."
echo "▶️ Create directories..."
mkdir -p "$RELEASES_DIR" "$SHARED_DIR/storage" "$SHARED_DIR/bootstrap_cache"

mkdir -p "$SHARED_DIR/storage/framework/views"
mkdir -p "$SHARED_DIR/storage/framework/cache"
mkdir -p "$SHARED_DIR/storage/framework/sessions"
mkdir -p "$SHARED_DIR/storage/logs"

echo "▶️ Unpacking release..."
mkdir "$RELEASE_DIR"
tar -xzf "$APP_BASE/$ARCHIVE_NAME" -C "$RELEASE_DIR"
rm "$APP_BASE/$ARCHIVE_NAME"

echo "▶️ Symlink storage..."
rm -rf "$RELEASE_DIR/storage"
ln -s "$SHARED_DIR/storage" "$RELEASE_DIR/storage"

rm -rf "$RELEASE_DIR/bootstrap/cache"
ln -s "$SHARED_DIR/bootstrap_cache" "$RELEASE_DIR/bootstrap/cache"

ln -sf "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

echo "▶️ Optimizing application..."
cd "$RELEASE_DIR"
php artisan optimize:clear
php artisan optimize
php artisan storage:link

echo "▶️ Migrating database..."
php artisan migrate --force

echo "▶️ Symlink current..."
ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"

echo "▶️ Cleaning old releases..."
cd "$RELEASES_DIR"
ls -dt */ | tail -n +6 | xargs -r rm -rf

echo "✅ Deploy finished: $APP_NAME - $NOW"
