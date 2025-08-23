# 🚀 Deployment Guide

Panduan lengkap untuk setup deployment otomatis ke VPS menggunakan GitHub Actions untuk aplikasi Rewire.

## 📋 Daftar Isi

- [Prerequisites](#prerequisites)
- [Setup SSH Key](#setup-ssh-key)
- [Konfigurasi VPS](#konfigurasi-vps)
- [GitHub Secrets](#github-secrets)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Web Server Setup](#web-server-setup)
- [SSL Certificate](#ssl-certificate)
- [Permissions & Services](#permissions--services)
- [Testing Deployment](#testing-deployment)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

Sebelum memulai, pastikan Anda memiliki:

- ✅ VPS dengan Ubuntu 20.04+ atau Debian 11+
- ✅ Domain yang sudah mengarah ke VPS
- ✅ Access root atau sudo ke VPS
- ✅ Repository GitHub dengan kode Rewire
- ✅ Flux UI license (jika menggunakan Pro)

---

## 🔑 Setup SSH Key

### 1. Generate SSH Key Pair

Di komputer local:

```bash
# Generate SSH key khusus untuk deployment
ssh-keygen -t rsa -b 4096 -C "github-actions-deploy" -f ~/.ssh/rewire_deploy

# Copy public key ke clipboard
cat ~/.ssh/rewire_deploy.pub
```

### 2. Setup di VPS

```bash
# Login ke VPS
ssh root@your_vps_ip

# Buat user khusus untuk deployment (opsional tapi recommended)
adduser deployer
usermod -aG sudo deployer

# Switch ke user deployer
su - deployer

# Setup SSH directory
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Tambahkan public key
nano ~/.ssh/authorized_keys
# Paste public key yang sudah di-copy
chmod 600 ~/.ssh/authorized_keys
```

### 3. Test Koneksi

```bash
# Test dari local machine
ssh -i ~/.ssh/rewire_deploy deployer@your_vps_ip
```

---

## ⚙️ Konfigurasi VPS

### 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Install Dependencies

```bash
# Install PHP 8.2
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update

sudo apt install -y \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-mbstring \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-sqlite3
```

```bash
# Install Nginx
sudo apt install nginx -y

# Install MySQL
sudo apt install mysql-server -y

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Git
sudo apt install git -y
```

### 3. Setup Project Directory

```bash
# Buat directory aplikasi
sudo mkdir -p /var/www/rewire
sudo chown deployer:deployer /var/www/rewire

# Clone repository (initial)
cd /var/www/rewire
git clone https://github.com/your-username/rewire.git .

# Install dependencies (initial setup)
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

---

## 🔐 GitHub Secrets

Buka GitHub repository → **Settings** → **Secrets and variables** → **Actions**, tambahkan secrets berikut:

### VPS Connection Secrets

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `VPS_HOST` | IP address VPS | `123.456.789.0` |
| `VPS_USERNAME` | Username untuk SSH | `deployer` |
| `VPS_SSH_KEY` | Private key SSH | Content dari `~/.ssh/rewire_deploy` |
| `VPS_PORT` | SSH port | `22` |
| `VPS_PATH` | Path aplikasi di VPS | `/var/www/rewire` |

### Application Secrets

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `FLUX_USERNAME` | Flux UI username/email | `your@email.com` |
| `FLUX_LICENSE_KEY` | Flux UI license key | `flux_xxxxxx` |

### Database Secrets (Opsional)

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `DB_PASSWORD` | Production DB password | `super_secure_password` |

---

## 📝 Environment Configuration

### 1. Buat File Environment

```bash
cd /var/www/rewire
cp .env.example .env
```

### 2. Edit .env untuk Production

```bash
nano .env
```

```env
# Application
APP_NAME=Rewire
APP_ENV=production
APP_KEY=base64:your_generated_key
APP_DEBUG=false
APP_URL=https://rewire.web.id

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rewire
DB_USERNAME=rewire_user
DB_PASSWORD=your_secure_password

# Cache & Session
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@rewire.web.id
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

---

## 🗄️ Database Setup

### 1. Secure MySQL Installation

```bash
sudo mysql_secure_installation
```

### 2. Create Database & User

```bash
sudo mysql
```

```sql
-- Buat database
CREATE DATABASE rewire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user dan set privileges
CREATE USER 'rewire_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON rewire.* TO 'rewire_user'@'localhost';
FLUSH PRIVILEGES;

-- Test koneksi
USE rewire;
SHOW TABLES;
EXIT;
```

### 3. Run Migrations

```bash
cd /var/www/rewire
php artisan migrate --force
php artisan db:seed --force
```

---

## 🌐 Web Server Setup

### 1. Konfigurasi Nginx

```bash
sudo nano /etc/nginx/sites-available/rewire
```

```nginx
server {
    server_name rewire.web.id www.rewire.web.id;
    root /var/www/rewire/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html;
    charset utf-8;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt  {
        access_log off;
        log_not_found off;
    }

    # Flux assets configuration
    location ~ ^/flux/flux(\.min)?\.(js|css)$ {
        expires off;
        try_files $uri $uri/ /index.php?$query_string;
    }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Livewire assets configuration
    location = /livewire/livewire.js {
        expires off;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /livewire/livewire.min.js {
        expires off;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Security headers
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/rewire.web.id/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/rewire.web.id/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot
}

server {
    if ($host = www.rewire.web.id) {
        return 301 https://$host$request_uri;
    } # managed by Certbot

    if ($host = rewire.web.id) {
        return 301 https://$host$request_uri;
    } # managed by Certbot

    listen 80;
    server_name rewire.web.id www.rewire.web.id;
    return 404; # managed by Certbot
}
```

### 2. Enable Site

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/rewire /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### 3. Setup PHP-FPM

```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Cari dan ubah:
```ini
user = www-data
group = www-data
listen.owner = www-data
listen.group = www-data
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

---

## 🔒 SSL Certificate

### 1. Install Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Generate Certificate

```bash
# Pastikan domain sudah mengarah ke VPS
sudo certbot --nginx -d rewire.web.id -d www.rewire.web.id
```

### 3. Auto-renewal

```bash
# Test auto-renewal
sudo certbot renew --dry-run

# Setup cron job (sudah otomatis biasanya)
sudo crontab -e
# Tambahkan jika belum ada:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## ⚙️ Permissions & Services

### 1. Set File Permissions

```bash
cd /var/www/rewire

# Set ownership
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chown -R deployer:deployer .

# Set permissions
chmod -R 775 storage bootstrap/cache
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod +x artisan
```

### 2. Setup Queue Worker (Opsional)

```bash
sudo nano /etc/systemd/system/rewire-worker.service
```

```ini
[Unit]
Description=Rewire Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
RestartSec=10
ExecStart=/usr/bin/php /var/www/rewire/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/rewire
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
# Enable dan start service
sudo systemctl enable rewire-worker
sudo systemctl start rewire-worker

# Check status
sudo systemctl status rewire-worker
```

### 3. Setup Scheduler (Opsional)

```bash
# Add to crontab
sudo crontab -e -u www-data
```

Tambahkan:
```bash
* * * * * cd /var/www/rewire && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Testing Deployment

### 1. Manual Test

```bash
# Test dari local
ssh -i ~/.ssh/rewire_deploy deployer@your_vps_ip "cd /var/www/rewire && php artisan --version"
```

### 2. GitHub Actions Test

1. Push kode ke branch `main`
2. Buka GitHub repository → **Actions**
3. Monitor workflow: `linter` → `tests` → `deploy`
4. Check logs jika ada error

### 3. Website Test

- Buka `https://rewire.web.id`
- Test registrasi user baru
- Test login/logout
- Check dashboard functionality

---

## 🔍 Troubleshooting

### Common Issues

#### 1. SSH Connection Failed
```bash
# Debug SSH connection
ssh -v -i ~/.ssh/rewire_deploy deployer@your_vps_ip

# Check SSH service di VPS
sudo systemctl status ssh
sudo systemctl restart ssh
```

#### 2. Permission Denied
```bash
# Fix file permissions
cd /var/www/rewire
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

#### 3. Database Connection Error
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL service
sudo systemctl status mysql
sudo systemctl restart mysql
```

#### 4. Composer Authentication Error
```bash
# Clear composer cache
composer clear-cache

# Re-authenticate Flux
composer config http-basic.composer.fluxui.dev username license_key
```

### Log Locations

```bash
# Application logs
tail -f /var/www/rewire/storage/logs/laravel.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.2-fpm.log

# System logs
journalctl -f -u nginx
journalctl -f -u php8.2-fpm
```

### Useful Commands

```bash
# Restart services
sudo systemctl restart nginx php8.2-fpm mysql

# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-optimize for production (Laravel 12 single command)
php artisan optimize

# Or individual commands if needed:
# php artisan config:cache
# php artisan route:cache  
# php artisan view:cache
# php artisan event:cache

# Check queue status
php artisan queue:work --once
sudo systemctl status rewire-worker
```

---

## 📚 Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Nginx Configuration Guide](https://nginx.org/en/docs/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Let's Encrypt Documentation](https://letsencrypt.org/docs/)

---

## 🛡️ Security Checklist

- [ ] Firewall dikonfigurasi (port 22, 80, 443)
- [ ] SSH key authentication (disable password auth)
- [ ] SSL certificate installed
- [ ] Database user dengan privileges minimal
- [ ] File permissions set dengan benar
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Error reporting minimal untuk production
- [ ] Backup strategy implemented

---

**Happy Deploying! 🚀**

> Jika ada masalah, check troubleshooting section atau buat issue di repository.