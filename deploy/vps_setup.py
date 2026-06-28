import paramiko
import os

host = '72.60.140.55'
port = 22
username = 'root'
password = 'Germaninho9949?'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)

# Step 1: Delete rate limits
print('=== STEP 1: RESET RATE LIMITS ===')
ssh.exec_command('rm -f /root/astrapay/data/rate_limits.json')
_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/data/')
print(stdout.read().decode())

# Also delete any public API rate limit files
ssh.exec_command('rm -f /root/astrapay/backend/storage/ratelimit_*.json 2>/dev/null; echo done')

# Step 2: Check current VPS structure
print('=== STEP 2: CHECK VPS STRUCTURE ===')
_, stdout, _ = ssh.exec_command('find /root/astrapay -type f -name "*.php" | sort')
print(stdout.read().decode())

# Step 3: Upload api-docs.php and api-keys.php to VPS
print('=== STEP 3: UPLOAD MISSING PAGES ===')

local_base = r'C:\Users\Pichau\Desktop\checkers2\astrapay'

# Upload the templates to public/templates/pages/
files_to_upload = [
    (os.path.join(local_base, 'templates', 'pages', 'api-docs.php'), '/root/astrapay/templates/pages/api-docs.php'),
    (os.path.join(local_base, 'templates', 'pages', 'api-keys.php'), '/root/astrapay/templates/pages/api-keys.php'),
]

sftp = ssh.open_sftp()
for local_path, remote_path in files_to_upload:
    if os.path.exists(local_path):
        print(f'Uploading {local_path} -> {remote_path}')
        sftp.put(local_path, remote_path)
    else:
        print(f'MISSING: {local_path}')

sftp.close()

# Step 4: Verify uploads
print('=== STEP 4: VERIFY ===')
_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/templates/pages/')
print(stdout.read().decode())

ssh.close()
print('\n=== ALL DONE ===')
