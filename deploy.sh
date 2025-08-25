#!/bin/bash

set -e
set -o pipefail

APP_USER="deployer"
APP_GROUP="www-data"
APP_BASE="/home/$APP_USER/laravel"
PROJECT_NAME="rewire.web.id"
PROJECT_DIR="$APP_BASE/$PROJECT_NAME"
RELEASES_DIR="$PROJECT_DIR/releases"
SHARED_DIR="$PROJECT_DIR/shared"
CURRENT_LINK="$PROJECT_DIR/current"
NOW=$(date +%Y-%m-%d-%H%M%S)-$(openssl rand -hex 3)
RELEASE_DIR="$RELEASES_DIR/$NOW"
ARCHIVE_NAME="release.tar.gz"

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

echo "▶️ Fix .env file formatting..."
cd "$RELEASE_DIR"

# Convert CRLF to LF and fix missing quotes
sed -i 's/\r$//' .env
sed -i 's/^ADMIN_NAME=Zachran Razendra$/ADMIN_NAME="Zachran Razendra"/' .env
sed -i 's/^MAIL_FROM_NAME=Rewire$/MAIL_FROM_NAME="Rewire"/' .env
sed -i 's/^VITE_APP_NAME=Rewire$/VITE_APP_NAME="Rewire"/' .env

echo "Fixed .env file:"
grep -n "Zachran\|FROM_NAME\|VITE_APP" .env || echo "No matches"

echo "▶️ Migrating database..."
php artisan migrate --force

echo "▶️ Optimizing application..."
php artisan optimize:clear
php artisan optimize
php artisan storage:link

echo "▶️ Symlink current..."
ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"

echo "▶️ Cleaning old releases..."
cd "$RELEASES_DIR"
ls -dt */ | tail -n +6 | xargs -r rm -rf

echo "✅ Deploy finished: $NOW"
