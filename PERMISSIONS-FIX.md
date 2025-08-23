# 🔐 Permissions Fix for Deployment

## Issue: Permission Denied on .env.backup

**Error**: `cp: cannot create regular file '.env.backup': Permission denied`

**Root Cause**: The deployment user doesn't have write permissions to the application directory.

## ✅ Solutions Applied

### 1. Pre-deployment Permissions Check
Added to all deployment workflows:

```bash
# Check and fix permissions first
echo "🔐 Checking and fixing permissions..."
sudo chown -R $USER:$USER .
chmod u+w . .env 2>/dev/null || true
```

### 2. Safe Environment Backup
Replaced direct copy with conditional backup:

```bash
# Backup environment
echo "💾 Backing up environment..."
if [ -f .env ]; then
  cp .env .env.backup
  echo "✅ Environment file backed up"
else
  echo "⚠️  No .env file found, creating from .env.example"
  cp .env.example .env || echo "No .env.example found"
fi
```

### 3. Proper Laravel Permissions
Enhanced permission setting:

```bash
# Set proper permissions
echo "🔐 Setting proper permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chown -R $USER:$USER .
chmod -R 775 storage bootstrap/cache
chmod u+w .env
```

## 🛠️ Manual Fix (if needed)

If you still encounter permission issues, run these commands on VPS:

### 1. Fix Application Directory Ownership
```bash
# SSH to VPS as deployment user
ssh zachranraze@your_vps_ip

# Navigate to application directory
cd /path/to/your/application

# Fix ownership
sudo chown -R zachranraze:zachranraze .
sudo chown -R www-data:www-data storage bootstrap/cache

# Set proper permissions
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chmod u+w .env
```

### 2. Add User to www-data Group (if needed)
```bash
# Add deployment user to www-data group
sudo usermod -a -G www-data zachranraze

# Verify group membership
groups zachranraze
```

### 3. Set Proper Directory Permissions
```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Laravel specific permissions
chmod -R 775 storage bootstrap/cache
chmod u+w .env
chmod +x artisan
```

## 🔍 Permission Troubleshooting

### Check Current Permissions
```bash
# Check application directory ownership
ls -la /path/to/application

# Check specific files
ls -la .env storage/ bootstrap/cache/

# Check user and groups
whoami
groups
```

### Debug Deployment User
```bash
# Check if user can write to directory
touch test-write.txt && rm test-write.txt && echo "✅ Write permission OK" || echo "❌ No write permission"

# Check sudo permissions
sudo -l
```

## 📋 Files Modified

1. **`.github/workflows/deploy.yml`**
   - Added permissions check before backup
   - Enhanced permission setting after deployment

2. **`.github/workflows/deploy-debug.yml`**
   - Same improvements for debugging workflow

3. **`.github/workflows/deploy-password.yml`** (if exists)
   - Will need similar updates

## ✅ Verification

After applying these fixes:

1. **Run SSH Key Verification**:
   ```bash
   # GitHub Actions → SSH Key Verification & Fix → Run workflow
   ```

2. **Test Deploy Debug**:
   ```bash
   # GitHub Actions → Deploy Debug → Run workflow
   ```

3. **Full Deployment Test**:
   ```bash
   # Push to deploy branch → Monitor deployment workflow
   ```

## 🚨 Common Permission Issues

### Issue 1: Laravel Storage Permissions
**Symptoms**: Cache/session errors
**Fix**: 
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue 2: Git Permission Conflicts
**Symptoms**: Git operations fail during deployment
**Fix**:
```bash
sudo chown -R $USER:$USER .git
```

### Issue 3: Composer Cache Permissions
**Symptoms**: Composer fails during install
**Fix**:
```bash
sudo chown -R $USER:$USER ~/.composer
```

The permission fixes in the workflows should resolve the `.env.backup` creation issue and provide more robust deployment error handling.