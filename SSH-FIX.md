# 🔧 SSH Authentication Fix Guide

## Error: Permission denied (publickey)

SSH authentication gagal karena private key di GitHub Secrets tidak match dengan public key di VPS.

## ✅ Step-by-Step Solution

### 1. Verify Current SSH Key on VPS

Login ke VPS dan check authorized_keys:

```bash
# Login ke VPS
ssh username@your_vps_ip

# Check authorized_keys content
cat ~/.ssh/authorized_keys

# Check permissions
ls -la ~/.ssh/
ls -la ~/.ssh/authorized_keys
```

### 2. Generate New SSH Key Pair (Recommended)

Di local machine atau VPS, generate key pair baru:

```bash
# Generate new SSH key pair (tanpa passphrase!)
ssh-keygen -t rsa -b 4096 -C "github-actions@rewire" -f ~/.ssh/rewire_github_actions

# IMPORTANT: Jangan set passphrase, tekan Enter saja ketika ditanya
```

### 3. Add Public Key to VPS

```bash
# Copy public key content
cat ~/.ssh/rewire_github_actions.pub

# Login ke VPS dan tambahkan ke authorized_keys
ssh username@your_vps_ip

# Di VPS:
echo "ssh-rsa AAAAB3NzaC1yc2E... your-public-key-here" >> ~/.ssh/authorized_keys

# Set proper permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### 4. Update GitHub Secrets

Copy **private key** (bukan public key!) ke GitHub Secrets:

```bash
# Display private key - copy seluruh output ini
cat ~/.ssh/rewire_github_actions
```

**GitHub Repository → Settings → Secrets and Variables → Actions:**
- Update `VPS_SSH_KEY` dengan **seluruh content private key** termasuk:
  ```
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (banyak baris base64)
  ...
  -----END RSA PRIVATE KEY-----
  ```

### 5. Test SSH Connection Manually

Sebelum run GitHub Actions, test dulu manual:

```bash
# Test koneksi dari local machine
ssh -i ~/.ssh/rewire_github_actions -p YOUR_PORT username@your_vps_ip 'whoami'

# Jika berhasil, akan tampil username
```

### 6. Verify Key Match

Pastikan private dan public key match:

```bash
# Extract public key from private key
ssh-keygen -y -f ~/.ssh/rewire_github_actions

# Bandingkan output dengan yang ada di VPS authorized_keys
ssh username@your_vps_ip 'cat ~/.ssh/authorized_keys'
```

## 🚨 Common Issues

### Issue 1: Key Format
- Private key di GitHub Secrets harus include header dan footer lengkap
- Tidak boleh ada extra spaces atau newlines

### Issue 2: Permissions
```bash
# Di VPS, pastikan permissions benar:
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### Issue 3: Multiple Keys
Jika ada multiple keys di authorized_keys, pastikan format benar (satu key per line).

### Issue 4: User Mismatch
Pastikan `VPS_USERNAME` di GitHub Secrets sama dengan user di VPS yang punya authorized_keys.

## 🔍 Debug Commands

```bash
# Check SSH service di VPS
sudo systemctl status ssh
sudo systemctl restart ssh

# Monitor SSH logs saat testing
sudo tail -f /var/log/auth.log

# Test dengan verbose debug
ssh -vvv -i ~/.ssh/rewire_github_actions -p PORT username@host
```

## ✅ Verification Checklist

- [ ] New SSH key pair generated (tanpa passphrase)
- [ ] Public key added to VPS `~/.ssh/authorized_keys`
- [ ] Permissions set correct (700 untuk .ssh, 600 untuk authorized_keys)
- [ ] Private key copied to GitHub Secret `VPS_SSH_KEY` (dengan header/footer)
- [ ] Manual SSH test berhasil
- [ ] Public key dari private key match dengan yang di VPS
- [ ] `VPS_USERNAME` match dengan user di VPS

Setelah semua step ini selesai, run ulang "Deploy Debug" workflow!