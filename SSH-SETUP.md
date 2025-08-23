# SSH Key Setup for GitHub Actions Deployment

## Problem: SSH Authentication Failed

The error `Permission denied (publickey)` indicates that the SSH key in GitHub Secrets is not properly formatted or not matching the public key on the VPS.

## Solution Steps:

### 1. Generate New SSH Key Pair (if needed)

On your local machine or VPS:

```bash
# Generate new SSH key pair
ssh-keygen -t rsa -b 4096 -C "github-actions@rewire" -f ~/.ssh/rewire_deploy

# Or use Ed25519 (recommended)
ssh-keygen -t ed25519 -C "github-actions@rewire" -f ~/.ssh/rewire_deploy
```

### 2. Add Public Key to VPS

```bash
# Copy public key to VPS authorized_keys
cat ~/.ssh/rewire_deploy.pub >> ~/.ssh/authorized_keys

# Or if you're on the VPS already:
echo "ssh-rsa AAAAB3NzaC1yc2E... your-public-key" >> ~/.ssh/authorized_keys

# Set proper permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### 3. Add Private Key to GitHub Secrets

1. **Get the private key content:**

```bash
# Display private key (copy this entire output)
cat ~/.ssh/rewire_deploy
```

2. **Add to GitHub Secrets:**
   - Go to: GitHub Repository → Settings → Secrets and Variables → Actions
   - Update `VPS_SSH_KEY` with the **entire private key content** including:
     - `-----BEGIN PRIVATE KEY-----` (first line)
     - All the key content (middle lines)
     - `-----END PRIVATE KEY-----` (last line)

### 4. Verify SSH Key Format

The private key should look like one of these formats:

**RSA Format:**
```
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA...
(many lines of base64)
...
-----END RSA PRIVATE KEY-----
```

**OpenSSH Format:**
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAA...
(many lines of base64)
...
-----END OPENSSH PRIVATE KEY-----
```

**PKCS#8 Format:**
```
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC...
(many lines of base64)
...
-----END PRIVATE KEY-----
```

### 5. Test SSH Connection Manually

Before running GitHub Actions, test SSH manually:

```bash
# Test from local machine
ssh -i ~/.ssh/rewire_deploy -p YOUR_PORT username@your-vps-ip 'whoami'

# Test with verbose output for debugging
ssh -vvv -i ~/.ssh/rewire_deploy -p YOUR_PORT username@your-vps-ip 'whoami'
```

### 6. Check VPS SSH Configuration

On your VPS, verify `/etc/ssh/sshd_config`:

```bash
# Check SSH daemon config
sudo nano /etc/ssh/sshd_config

# Ensure these settings:
PubkeyAuthentication yes
AuthorizedKeysFile .ssh/authorized_keys
PasswordAuthentication no  # Optional, for security

# Restart SSH service after changes
sudo systemctl restart ssh
```

### 7. Debug VPS SSH Logs

Monitor SSH logs while testing:

```bash
# Monitor SSH logs in real-time
sudo tail -f /var/log/auth.log

# Or check recent authentication attempts
sudo grep "sshd" /var/log/auth.log | tail -20
```

## Common Issues:

1. **Wrong Permissions:** SSH keys must have 600 permissions
2. **Wrong User:** Make sure the username matches the VPS user
3. **Wrong Port:** Verify the SSH port in GitHub Secrets
4. **Key Format:** GitHub Secrets doesn't preserve newlines properly sometimes
5. **Multiple Keys:** VPS might be expecting different key format

## Testing with GitHub Actions Debug Workflow

Run the `deploy-debug.yml` workflow manually to see detailed SSH debugging information:

1. Go to GitHub Actions tab
2. Select "Deploy Debug" workflow  
3. Click "Run workflow"
4. Check the SSH key validation and connection test results

The debug workflow will show:
- SSH key format validation
- Multiple connection attempt methods
- Detailed error messages
- System environment information