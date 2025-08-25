# Zero Downtime Deployment for Laravel with GitHub Actions

## What is Zero Downtime Deployment?

Zero downtime deployment means updating your app without users noticing any interruption — the site stays online and responsive while the new version is being deployed.

## 0. Create User (optional — you can use your non-root user)

```bash
# Create user with proper primary group
sudo adduser deployer --ingroup www-data
sudo usermod -aG sudo deployer

# Secure sudo access
echo "deployer ALL=(ALL:ALL) ALL" | sudo tee /etc/sudoers.d/deployer
echo 'Defaults:deployer !requiretty' | sudo tee -a /etc/sudoers.d/deployer

# Fix home directory permissions
sudo chmod 711 /home/deployer
```

## 1. Initial Server Setup

```bash
# Update the system
apt update
sudo apt install -y nginx php-fpm mariadb-server ufw fail2ban acl
sudo apt install -y php8.3-{cli,common,curl,xml,mbstring,zip,mysql,gd,intl,bcmath,redis,imagick,opcache,tokenizer,dom,fileinfo}
sudo systemctl restart php8.3-fpm
```

## 2. Configure Nginx

Create a new Nginx configuration file:

```bash
sudo nano /etc/nginx/sites-available/laravel
```

Add the following configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /home/deployer/laravel/your-domain.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Permissions-Policy "geolocation=(), midi=(), sync-xhr=(), microphone=(), camera=(), magnetometer=(), gyroscope=(), fullscreen=(self), payment=()";
    server_tokens off;

    index index.php;
    charset utf-8;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    client_max_body_size 100M;

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        log_not_found off;
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_read_timeout 300;
        
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss application/atom+xml image/svg+xml;
    gzip_min_length 1024;
    gzip_buffers 16 8k;
    gzip_disable "MSIE [1-6]\.";

    # Flux assets configuration
    location ~ ^/flux/flux(\.min)?\.(js|css)$ {
        expires off;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Livewire assets configuration
    location ~ ^/livewire/livewire\.(js|min\.js)$ {
        expires off;
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 3. Update PHP-FPM Configuration

```bash
nano /etc/php/8.3/fpm/pool.d/www.conf
```

Replace it with the following configuration and restart PHP-FPM:

```ini
[www]
user = deployer
group = www-data

listen = /var/run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.process_idle_timeout = 10s
pm.max_requests = 500

php_admin_value[open_basedir] = /home/deployer/laravel/current/:/home/deployer/laravel/releases/:/home/deployer/laravel/shared/:/tmp/:/var/lib/php/sessions/
php_admin_value[disable_functions] = "exec,passthru,shell_exec,system,proc_open,popen"
php_admin_flag[expose_php] = off
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 120
php_admin_value[realpath_cache_size] = 4096K
php_admin_value[realpath_cache_ttl] = 600
php_admin_value[opcache.enable] = 1
php_admin_value[opcache.memory_consumption] = 128
```

## 4. Update PHP Configuration

```bash
nano /etc/php/8.3/fpm/php.ini
```

Replace it with the following configuration:

```ini
[PHP]
expose_php = Off
max_execution_time = 30
max_input_time = 60
memory_limit = 256M
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php8.3-fpm.log

opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=32
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.enable_cli=0
opcache.jit_buffer_size=256M
opcache.jit=1235

realpath_cache_size=4096K
realpath_cache_ttl=600

session.gc_probability=1
session.gc_divisor=100
session.gc_maxlifetime=1440
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"

upload_max_filesize = 64M
post_max_size = 64M
file_uploads = On

max_input_vars = 5000
request_order = "GP"
variables_order = "GPCS"

[Date]
date.timezone = Europe/Warsaw
```

## 5. Set Up Directory Structure

```bash
# Create structure with proper permissions
sudo mkdir -p /home/deployer/laravel/{releases,shared}
sudo chown -R deployer:www-data /home/deployer/laravel
sudo chmod -R 2775 /home/deployer/laravel

# Shared folders setup
sudo mkdir -p /home/deployer/laravel/shared/storage/{app,framework,logs}
sudo mkdir -p /home/deployer/laravel/shared/storage/framework/{cache,sessions,views}
sudo chmod -R 775 /home/deployer/laravel/shared

# Set ACL for future files
sudo setfacl -Rdm g:www-data:rwx /home/deployer/laravel
```

## 6. Set Up SSH Key for GitHub Actions (as deployer user)

```bash
# Create SSH directory
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy"

# Add public key to authorized_keys
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Display the private key
cat ~/.ssh/id_rsa
```

## 7. Add GitHub Secrets

Add the following secrets to your GitHub repository:

- **SSH_HOST**: Your VPS IP address or domain
- **SSH_USER**: Your VPS username  
- **SSH_KEY**: The private SSH key generated above
- **SSH_PORT**: The SSH port (default is 22)

Add variable for .env production file:

- **ENV_FILE**: The contents of your .env file

**Important:** Ensure all environment variables with spaces or special characters are properly quoted:

```env
# ✅ Correct format
APP_NAME="Your App Name"
MAIL_FROM_NAME="Your Name"

# ❌ Incorrect format (will cause deployment errors)
APP_NAME=Your App Name
MAIL_FROM_NAME=[Your Name]
```

## 8. Set Up SSL with Let's Encrypt (Optional but Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com

# Set up auto-renewal
sudo systemctl status certbot.timer
```

## 9. Deploy

In main directory create script `deploy.sh`:

```bash
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

echo "✅ Deploy finished: $NOW"
```

And `.github/workflows/workflow.yaml`:

```yaml
name: Zero Downtime Deployment

on:
  push:
    branches:
      - deploy

jobs:
  test:
    name: 🧪 Test & Lint
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: laravel_test
          MYSQL_ROOT_PASSWORD: secret
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping --silent"
          --health-interval=5s
          --health-timeout=2s
          --health-retries=2
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, dom, fileinfo, mysql, zip, gd, intl, redis, imagick
          coverage: xdebug
          tools: composer:v2

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: 'npm'

      - name: Copy .env.testing
        run: cp .env.testing .env

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: |
            vendor
            ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-progress

      - name: Install NPM dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Run code quality checks
        run: |
          composer larastan
          composer pint
          npm run format
          npm run types
          npm run lint

      - name: Run tests (with Pest)
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: laravel_test
          DB_USERNAME: root
          DB_PASSWORD: secret
          REDIS_HOST: 127.0.0.1
          REDIS_PORT: 6379
          SESSION_DRIVER: array
        run: ./vendor/bin/pest

  build:
    name: 🏗️ Build Release
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '22'
          cache: 'npm'

      - name: Install NPM dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, dom, fileinfo, mysql, zip, gd, intl, redis, imagick
          tools: composer:v2

      - name: Install Composer dependencies
        run: composer install --no-dev --prefer-dist --no-interaction --no-progress

      - name: Create release archive
        run: |
          # Create temporary folder named "release" and copy project without unnecessary directories
          mkdir release
          shopt -s extglob
          cp -r !(release|.git|tests|node_modules|release.tar.gz) release/
          tar -czf release.tar.gz -C release .
          rm -rf release

      - name: Upload release artifact
        uses: actions/upload-artifact@v4
        with:
          name: release
          path: release.tar.gz

  deploy:
    name: 🚀 Deploy to Server
    needs: build
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup SSH Key
        uses: webfactory/ssh-agent@v0.9.1
        with:
          ssh-private-key: ${{ secrets.SSH_KEY }}

      - name: Setup known_hosts
        run: |
          mkdir -p ~/.ssh
          ssh-keyscan -p ${{ secrets.SSH_PORT }} ${{ secrets.SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Download release artifact
        uses: actions/download-artifact@v4
        with:
          name: release
          path: .

      - name: Create .env file from GitHub Variables
        run: |
          echo "${{ vars.ENV_FILE }}" > .env

      - name: Upload release to server
        run: |
          scp -vvv -P ${{ secrets.SSH_PORT }} release.tar.gz ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}:/home/${{ secrets.SSH_USER }}/laravel/

      - name: Upload .env file to shared directory
        run: |
          scp -P ${{ secrets.SSH_PORT }} .env ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}:/home/${{ secrets.SSH_USER }}/laravel/shared/.env

      - name: Run deploy script on server
        run: |
          ssh -p ${{ secrets.SSH_PORT }} ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }} 'bash -s' < ./deploy.sh
```

## 10. Final Steps

1. Push your code to the main branch to trigger the deployment.
2. Monitor the GitHub Actions workflow to ensure it completes successfully.
3. Check your website to verify the deployment.

## 11. Source Code

[https://github.com/Dommmin/laravel-dockerized](https://github.com/Dommmin/laravel-dockerized)

## Troubleshooting

- **Permission Issues**: Ensure all directories have the correct ownership and permissions.
- **Nginx Errors**: Check the Nginx error logs with `sudo tail -f /var/log/nginx/error.log`.
- **PHP-FPM Errors**: Check the PHP-FPM error logs with `sudo tail -f /var/log/php8.3-fpm.log`.
- **Deployment Failures**: Check the GitHub Actions logs for detailed error messages.

## Conclusion

This guide provides a comprehensive setup for deploying Laravel applications on a VPS with zero downtime. By following these steps, you can ensure a smooth and efficient deployment process.
