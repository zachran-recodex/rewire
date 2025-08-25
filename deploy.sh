#!/bin/bash

set -e
set -o pipefail

APP_USER="deployer"
APP_GROUP="www-data"
APP_BASE="/home/$APP_USER/laravel"
RELEASES_DIR="$APP_BASE/releases"
SHARED_DIR="$APP_BASE/shared"
CURRENT_LINK="$APP_BASE/current"
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

echo "▶️ Debug .env file..."
cd "$RELEASE_DIR"
echo "File exists: $(test -f .env && echo 'YES' || echo 'NO')"
echo "File size: $(wc -c < .env 2>/dev/null || echo 'ERROR')"
echo "Search for 'Zachran' lines:"
grep -n "Zachran" .env 2>/dev/null || echo "No Zachran found"
echo "Show lines with quotes:"
grep -n '"' .env 2>/dev/null | head -10 || echo "No quotes found"
echo "Show raw bytes around 'Zachran':"
grep -ao "Zachran.*" .env | hexdump -C || echo "Cannot hexdump"

echo "▶️ Optimizing application..."
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

echo "✅ Deploy finished: $NOW"
