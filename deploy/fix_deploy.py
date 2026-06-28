import paramiko
import os

host = '72.60.140.55'
port = 22
username = 'root'
password = 'Germaninho9949?'

vps_base = '/root/astrapay'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)

def exec_cmd(cmd):
    _, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    err = stderr.read().decode('utf-8', errors='replace')
    if err and 'Warning' not in err:
        print(f'  STDERR: {err[:300]}')
    return out

print('='*50)
print('Fix: Create test user & configure service')
print('='*50)

# Create test user with correct method name
print('\n--- Creating test user bbb@gmail.com ---')
create_user_cmd = f'''php -r "
define('DB_PATH', '{vps_base}/data/astrapay.db');
require_once '{vps_base}/backend/db.php';
\\$db = DB::getInstance();

\\$existing = \\$db->fetch('SELECT id FROM users WHERE email = ?', ['bbb@gmail.com']);
if (\\$existing) {{
    \\$hash = password_hash('fada123', PASSWORD_BCRYPT);
    \\$db->execute('UPDATE users SET password_hash = ?, name = ?, email_verified = 1, tier = ? WHERE email = ?', [\\$hash, 'BBB User', 'new', 'bbb@gmail.com']);
    echo 'User updated: bbb@gmail.com' . PHP_EOL;
}} else {{
    \\$hash = password_hash('fada123', PASSWORD_BCRYPT);
    \\$db->execute('INSERT INTO users (email, password_hash, name, email_verified, tier, created_at, updated_at) VALUES (?, ?, ?, 1, ?, datetime(\\\\'now\\\\'), datetime(\\\\'now\\\\'))', ['bbb@gmail.com', \\$hash, 'BBB User', 'new']);
    echo 'User created: bbb@gmail.com' . PHP_EOL;
}}

\\$user = \\$db->fetch('SELECT id, email, name, tier, email_verified FROM users WHERE email = ?', ['bbb@gmail.com']);
echo 'User record: ' . json_encode(\\$user) . PHP_EOL;

\\$count = \\$db->fetch('SELECT COUNT(*) as c FROM users');
echo 'Total users: ' . \\$count['c'] . PHP_EOL;
" 2>&1'''
print(exec_cmd(create_user_cmd))

# Update service file
print('\n--- Updating service to run from public/ directory ---')
sftp = ssh.open_sftp()
local_service = r'C:\Users\Pichau\Desktop\checkers2\astrapay\deploy\astrapay.service'
sftp.put(local_service, f'{vps_base}/deploy/astrapay.service')
sftp.close()

print(exec_cmd(f'cp {vps_base}/deploy/astrapay.service /etc/systemd/system/astrapay.service'))
print(exec_cmd('systemctl daemon-reload'))
print(exec_cmd('systemctl enable astrapay'))
print(exec_cmd('systemctl restart astrapay'))

# Check status
print('\n--- Service Status ---')
status = exec_cmd('systemctl status astrapay --no-pager 2>&1')
print(status[:1500])

# Verify endpoints
print('\n--- Verifying Endpoints ---')
print(exec_cmd('curl -s -o /dev/null -w "%{http_code}" http://localhost:9000/'))
print(exec_cmd('curl -s -o /dev/null -w "%{http_code}" http://localhost:9000/login'))
print(exec_cmd('curl -s -o /dev/null -w "%{http_code}" http://localhost:9000/register'))
print(exec_cmd('curl -s -o /dev/null -w "%{http_code}" http://localhost:9000/dashboard'))
print(exec_cmd('curl -s -o /dev/null -w "%{http_code}" http://localhost:9000/assets/css/app.css'))

# Test login
print('\n--- Testing Login API ---')
login_result = exec_cmd('curl -s -X POST http://localhost:9000/api/auth/login -H "Content-Type: application/json" -d \'{"email":"bbb@gmail.com","password":"fada123"}\'')
print(login_result[:500])

ssh.close()
print('\nDone!')
