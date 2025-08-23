# 🚀 Quick SSH Fix

## Problem Identified:
1. ❌ Using `root` user instead of `zachranraze`
2. ❌ SSH key has restrictions that block GitHub Actions
3. ❌ Wrong key format in GitHub Secrets

## Quick Fix Steps:

### 1. Login as correct user:
```bash
ssh zachranraze@your_vps_ip
```

### 2. Generate new unrestricted SSH key:
```bash
# Generate Ed25519 key (recommended)
ssh-keygen -t ed25519 -C "github-actions@rewire" -f ~/.ssh/github_deploy

# Or RSA if you prefer
ssh-keygen -t rsa -b 4096 -C "github-actions@rewire" -f ~/.ssh/github_deploy

# IMPORTANT: Don't set passphrase (press Enter when asked)
```

### 3. Add public key to authorized_keys WITHOUT restrictions:
```bash
# Add the new public key (clean, no restrictions)
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys

# Remove the old restricted key (optional)
# nano ~/.ssh/authorized_keys  # Delete the line with restrictions

# Set proper permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys ~/.ssh/github_deploy*
```

### 4. Get private key for GitHub Secret:
```bash
# Display private key - copy ALL of this to GitHub Secret VPS_SSH_KEY
cat ~/.ssh/github_deploy
```

### 5. Update GitHub Secrets:
Go to GitHub Repository → Settings → Secrets and Variables → Actions:

- **VPS_USERNAME**: `zachranraze` (not root!)
- **VPS_SSH_KEY**: [paste entire private key from step 4]
- **VPS_HOST**: [your VPS IP]
- **VPS_PORT**: `22`
- **VPS_PATH**: `/home/zachranraze/your-app-path` (not /root/)

### 6. Test manually:
```bash
# From your local machine, test the connection
ssh -i ~/.ssh/github_deploy zachranraze@your_vps_ip 'echo "SUCCESS: $(whoami)@$(hostname)"'
```

Expected output: `SUCCESS: zachranraze@recodex-server`

### 7. Update VPS_PATH if needed:
```bash
# Check where your application should be deployed
ls -la /home/zachranraze/
# Update VPS_PATH GitHub Secret accordingly
```

## Current authorized_keys Analysis:
Your current key has these restrictions:
```
no-port-forwarding,no-agent-forwarding,no-X11-forwarding,command="echo 'Please login as...'"
```

These restrictions prevent GitHub Actions from executing deployment commands. The new key should be added WITHOUT these restrictions.

## ✅ Verification:
After making these changes, run the "SSH Key Verification & Fix" workflow to confirm everything is working.