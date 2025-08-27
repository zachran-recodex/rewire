#!/bin/bash

set -e
set -o pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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
BACKUP_LINK="$APP_BASE/previous"
HEALTH_CHECK_URL="https://$APP_NAME"
MAX_HEALTH_RETRIES=5
HEALTH_RETRY_DELAY=10

# Function to log messages with colors
log_info() {
    echo -e "${BLUE}▶️ $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Function to rollback deployment
rollback() {
    log_error "Deployment failed. Rolling back..."

    if [ -L "$BACKUP_LINK" ] && [ -e "$BACKUP_LINK" ]; then
        log_info "Restoring previous release..."
        ln -sfn "$(readlink "$BACKUP_LINK")" "$CURRENT_LINK"
        log_success "Rollback completed successfully"
    else
        log_warning "No previous release found for rollback"
    fi

    # Clean up failed release
    if [ -d "$RELEASE_DIR" ]; then
        log_info "Cleaning up failed release..."
        rm -rf "$RELEASE_DIR"
    fi

    exit 1
}

# Function to check application health
health_check() {
    log_info "Performing health check..."

    for i in $(seq 1 $MAX_HEALTH_RETRIES); do
        if curl -f -s -o /dev/null --max-time 30 "$HEALTH_CHECK_URL"; then
            log_success "Health check passed (attempt $i/$MAX_HEALTH_RETRIES)"
            return 0
        else
            log_warning "Health check failed (attempt $i/$MAX_HEALTH_RETRIES)"
            if [ $i -lt $MAX_HEALTH_RETRIES ]; then
                sleep $HEALTH_RETRY_DELAY
            fi
        fi
    done

    log_error "Health check failed after $MAX_HEALTH_RETRIES attempts"
    return 1
}

# Set trap for cleanup on error
trap rollback ERR

echo -e "${GREEN}🚀 Deploying $APP_NAME...${NC}"

# Store current release as backup before deployment
if [ -L "$CURRENT_LINK" ] && [ -e "$CURRENT_LINK" ]; then
    log_info "Backing up current release..."
    ln -sfn "$(readlink "$CURRENT_LINK")" "$BACKUP_LINK"
fi

log_info "Creating directories..."
mkdir -p "$RELEASES_DIR" "$SHARED_DIR/storage" "$SHARED_DIR/bootstrap_cache"

mkdir -p "$SHARED_DIR/storage/framework/views"
mkdir -p "$SHARED_DIR/storage/framework/cache"
mkdir -p "$SHARED_DIR/storage/framework/sessions"
mkdir -p "$SHARED_DIR/storage/logs"

log_info "Unpacking release..."
mkdir "$RELEASE_DIR"
if ! tar -xzf "$APP_BASE/$ARCHIVE_NAME" -C "$RELEASE_DIR"; then
    log_error "Failed to extract archive"
    exit 1
fi
rm "$APP_BASE/$ARCHIVE_NAME"

log_info "Setting up symlinks..."
rm -rf "$RELEASE_DIR/storage"
ln -s "$SHARED_DIR/storage" "$RELEASE_DIR/storage"

rm -rf "$RELEASE_DIR/bootstrap/cache"
ln -s "$SHARED_DIR/bootstrap_cache" "$RELEASE_DIR/bootstrap/cache"

ln -sf "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

log_info "Setting file permissions..."
# Only set permissions, skip ownership change (user is already correct)
chmod -R 755 "$RELEASE_DIR"
chmod -R 775 "$RELEASE_DIR/storage" "$RELEASE_DIR/bootstrap/cache" 2>/dev/null || true

log_info "Installing/updating composer dependencies..."
cd "$RELEASE_DIR"

# Try to find composer in common locations
COMPOSER=""
if command -v composer >/dev/null 2>&1; then
    COMPOSER="composer"
elif command -v /usr/local/bin/composer >/dev/null 2>&1; then
    COMPOSER="/usr/local/bin/composer"
elif command -v /usr/bin/composer >/dev/null 2>&1; then
    COMPOSER="/usr/bin/composer"
elif [ -f "$HOME/.composer/vendor/bin/composer" ]; then
    COMPOSER="$HOME/.composer/vendor/bin/composer"
elif [ -f "$HOME/composer.phar" ]; then
    COMPOSER="php $HOME/composer.phar"
elif [ -f "./composer.phar" ]; then
    COMPOSER="php ./composer.phar"
else
    log_warning "Composer not found, downloading composer.phar..."
    curl -sS https://getcomposer.org/installer | php
    COMPOSER="php composer.phar"
fi

log_info "Using composer: $COMPOSER"
if ! $COMPOSER install --no-dev --optimize-autoloader --no-interaction; then
    log_error "Composer install failed"
    exit 1
fi

log_info "Optimizing application..."
if ! php artisan optimize:clear; then
    log_error "Failed to clear optimization cache"
    exit 1
fi

if ! php artisan config:cache; then
    log_error "Failed to cache config"
    exit 1
fi

if ! php artisan route:cache; then
    log_error "Failed to cache routes"
    exit 1
fi

if ! php artisan view:cache; then
    log_error "Failed to cache views"
    exit 1
fi

if ! php artisan storage:link; then
    log_warning "Storage link failed (may already exist)"
fi

log_info "Running database migrations..."
if ! php artisan migrate --force; then
    log_error "Database migration failed"
    exit 1
fi

log_info "Running database seeders..."
if ! php artisan db:seed --force; then
    log_warning "Database seeding failed or skipped"
fi

log_info "Restarting queue workers..."
if ! php artisan queue:restart; then
    log_warning "Queue restart failed (queue workers may not be running)"
fi

log_info "Switching to new release..."
ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"

# Perform health check
if ! health_check; then
    rollback
fi

log_info "Cleaning old releases (keeping last 5)..."
cd "$RELEASES_DIR"
ls -dt */ | tail -n +6 | xargs -r rm -rf

log_success "Deploy finished successfully: $APP_NAME - $NOW"
log_info "Release directory: $RELEASE_DIR"
log_info "Health check URL: $HEALTH_CHECK_URL"

# Clear trap since deployment succeeded
trap - ERR
