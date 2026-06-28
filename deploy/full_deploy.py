import paramiko
import os
import sys
import hashlib

host = '72.60.140.55'
port = 22
username = 'root'
password = 'Germaninho9949?'

local_base = r'C:\Users\Pichau\Desktop\checkers2\astrapay'
vps_base = '/root/astrapay'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)
sftp = ssh.open_sftp()

def upload(local_rel, remote_abs):
    local = os.path.join(local_base, local_rel)
    if not os.path.exists(local):
        print(f'  SKIP (missing local): {local_rel}')
        return
    print(f'  UPLOAD: {local_rel} -> {remote_abs}')
    sftp.put(local, remote_abs)

def exec_cmd(cmd):
    _, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode()
    err = stderr.read().decode()
    if err:
        print(f'  STDERR: {err[:200]}')
    return out

# ============================
# STEP 1: Upload all changed files
# ============================
print('\n' + '='*50)
print('STEP 1: Uploading all files')
print('='*50)

# Layout
upload('public/templates/layout.php', f'{vps_base}/public/templates/layout.php')

# Pages
pages = ['landing', 'login', 'register', 'dashboard', 'pix', 'transactions', 'settings', 'verify-email', 'api-docs', 'api-keys']
for page in pages:
    upload(f'public/templates/pages/{page}.php', f'{vps_base}/public/templates/pages/{page}.php')

# CSS
upload('public/assets/css/app.css', f'{vps_base}/public/assets/css/app.css')

# JS
upload('public/assets/js/app.js', f'{vps_base}/public/assets/js/app.js')

# Videos (placeholder)
upload('public/assets/hero-bg.mp4', f'{vps_base}/public/assets/hero-bg.mp4')
upload('public/assets/login-bg.mp4', f'{vps_base}/public/assets/login-bg.mp4')

# Logos
upload('public/assets/logobola.png', f'{vps_base}/public/assets/logobola.png')
upload('public/assets/logoescrita.png', f'{vps_base}/public/assets/logoescrita.png')

# Router
upload('public/index.php', f'{vps_base}/public/index.php')

# Backend files
backend_files = [
    'backend/config.php', 'backend/db.php', 'backend/init_db.php',
    'backend/auth.php', 'backend/router.php', 'backend/middleware.php',
    'backend/public_api.php', 'backend/pix_api.php', 'backend/api_keys.php',
    'backend/admin_api.php', 'backend/admin_auth.php', 'backend/stats_api.php',
    'backend/asaas.php', 'backend/webhook.php', 'backend/setup_admin.php',
    'backend/withdraw_api.php', 'backend/withdraw_processor.php',
    'backend/public/index.php',
]
for f in backend_files:
    upload(f, f'{vps_base}/{f}')

# Cron files
cron_files = ['cron/stats.php', 'cron/health.php', 'cron/cleanup.php', 'cron/withdrawals.php']
for f in cron_files:
    upload(f, f'{vps_base}/{f}')

# Admin templates
admin_pages = ['login', 'dashboard', 'users', 'transactions', 'audit', 'settings']
for page in admin_pages:
    upload(f'templates/pages/admin/{page}.php', f'{vps_base}/templates/pages/admin/{page}.php')

sftp.close()

# ============================
# STEP 2: Set permissions
# ============================
print('\n' + '='*50)
print('STEP 2: Setting permissions')
print('='*50)
print(exec_cmd(f'chmod -R 755 {vps_base} && echo "Permissions set"'))

# ============================
# STEP 3: Initialize database & create test user
# ============================
print('\n' + '='*50)
print('STEP 3: Database setup')
print('='*50)

# Check if DB exists
db_path = f'{vps_base}/data/astrapay.db'
print(exec_cmd(f'ls -la {db_path} 2>&1'))

# Run init_db.php
print('\n--- Running init_db.php ---')
result = exec_cmd(f'cd {vps_base} && php backend/init_db.php 2>&1')
print(result)

# Create test user bbb@gmail.com / fada123
print('\n--- Creating test user ---')
create_user_cmd = f'''php -r "
define('DB_PATH', '{vps_base}/data/astrapay.db');
require_once '{vps_base}/backend/db.php';
\\$db = DB::getInstance();

// Check if user exists
\\$existing = \\$db->fetchOne('SELECT id FROM users WHERE email = ?', ['bbb@gmail.com']);
if (\\$existing) {{
    // Update password
    \\$hash = password_hash('fada123', PASSWORD_BCRYPT);
    \\$db->execute('UPDATE users SET password_hash = ?, name = ?, email_verified = 1, tier = ? WHERE email = ?', [\\$hash, 'BBB User', 'new', 'bbb@gmail.com']);
    echo 'User updated: bbb@gmail.com\\n';
}} else {{
    // Create user
    \\$hash = password_hash('fada123', PASSWORD_BCRYPT);
    \\$db->execute('INSERT INTO users (email, password_hash, name, email_verified, tier, created_at, updated_at) VALUES (?, ?, ?, 1, ?, datetime(\\\\'now\\\\'), datetime(\\\\'now\\\\'))', ['bbb@gmail.com', \\$hash, 'BBB User', 'new']);
    echo 'User created: bbb@gmail.com\\n';
}}

// Verify
\\$user = \\$db->fetchOne('SELECT id, email, name, tier, email_verified FROM users WHERE email = ?', ['bbb@gmail.com']);
echo json_encode(\\$user) . '\\n';
" 2>&1'''
print(exec_cmd(create_user_cmd))

# ============================
# STEP 4: Restart service
# ============================
print('\n' + '='*50)
print('STEP 4: Restarting service')
print('='*50)

# Copy service file and restart
print(exec_cmd(f'cp {vps_base}/deploy/astrapay.service /etc/systemd/system/astrapay.service 2>&1'))
print(exec_cmd('systemctl daemon-reload 2>&1'))
print(exec_cmd('systemctl enable astrapay 2>&1'))
print(exec_cmd('systemctl restart astrapay 2>&1'))

# Check status
print('\n--- Service Status ---')
status = exec_cmd('systemctl status astrapay --no-pager -l 2>&1')
print(status[:2000])

ssh.close()

print('\n' + '='*50)
print('DEPLOY COMPLETE!')
print('='*50)
print('Verify: http://72.60.140.55:9000/')
print('Login:  bbb@gmail.com / fada123')
