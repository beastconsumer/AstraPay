import paramiko
import os

host = '72.60.140.55'
port = 22
username = 'root'
password = 'Germaninho9949?'

local_base = r'C:\Users\Pichau\Desktop\checkers2\astrapay'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)
sftp = ssh.open_sftp()

print('=== STEP 1: UPLOAD PAGE TEMPLATES ===')

# Upload api-docs.php page fragment
local_docs = os.path.join(local_base, 'public', 'templates', 'pages', 'api-docs.php')
remote_docs = '/root/astrapay/public/templates/pages/api-docs.php'
print(f'Uploading {local_docs} -> {remote_docs}')
sftp.put(local_docs, remote_docs)

# Upload api-keys.php page fragment
local_keys = os.path.join(local_base, 'public', 'templates', 'pages', 'api-keys.php')
remote_keys = '/root/astrapay/public/templates/pages/api-keys.php'
print(f'Uploading {local_keys} -> {remote_keys}')
sftp.put(local_keys, remote_keys)

print('=== STEP 2: UPDATE BACKEND api_keys.php ===')

local_backend = os.path.join(local_base, 'backend', 'api_keys.php')
remote_backend = '/root/astrapay/backend/api_keys.php'
print(f'Uploading {local_backend} -> {remote_backend}')
sftp.put(local_backend, remote_backend)

print('=== STEP 3: UPDATE public/index.php (add routes) ===')

# Read current VPS index.php
with sftp.open('/root/astrapay/public/index.php', 'r') as f:
    content = f.read().decode()

# Check if routes already exist
if "'/api-docs'" in content and "'/api-keys'" in content:
    print('Routes already exist in index.php, skipping.')
else:
    # Add new routes before the closing ] of $routes
    new_routes = """    '/api-docs'      => ['page' => 'api-docs',      'layout' => 'public', 'title' => 'API Docs - AstraPay'],
    '/api-keys'      => ['page' => 'api-keys',      'layout' => 'app',    'title' => 'API Keys - AstraPay'],
"""
    # Find the position of "];" after $routes array
    old_str = "'/settings'      => ['page' => 'settings',      'layout' => 'app',    'title' => 'Configuracoes - AstraPay'],\n];"
    new_str = "'/settings'      => ['page' => 'settings',      'layout' => 'app',    'title' => 'Configuracoes - AstraPay'],\n" + new_routes + "];"

    if old_str in content:
        content = content.replace(old_str, new_str)
        with sftp.open('/root/astrapay/public/index.php', 'w') as f:
            f.write(content.encode())
        print('Routes added to index.php.')
        print('New routes section:')
        print(new_routes)
    else:
        print('WARNING: Could not find route insertion point. Adding manually...')
        # Try alternate pattern
        old_str2 = "    '/settings'      => ['page' => 'settings',      'layout' => 'app',    'title' => 'Configuracoes - AstraPay'],\n];"
        new_str2 = "    '/settings'      => ['page' => 'settings',      'layout' => 'app',    'title' => 'Configuracoes - AstraPay'],\n" + new_routes + "];"
        if old_str2 in content:
            content = content.replace(old_str2, new_str2)
            with sftp.open('/root/astrapay/public/index.php', 'w') as f:
                f.write(content.encode())
            print('Routes added (pattern 2).')
        else:
            print('ERROR: Could not find insertion point!')

# Verify updated index.php
print('=== VERIFY index.php ===')
_, stdout, _ = ssh.exec_command('cat /root/astrapay/public/index.php')
print(stdout.read().decode())

print('=== STEP 4: VERIFY ALL FILES ===')
_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/public/templates/pages/')
print(stdout.read().decode())

# Also delete rate limits again and delete API rate limit files
ssh.exec_command('rm -f /root/astrapay/data/rate_limits.json')
ssh.exec_command('rm -f /root/astrapay/backend/storage/ratelimit_*.json 2>/dev/null; echo done')

sftp.close()
ssh.close()

print('=== UPDATE COMPLETE ===')
print('')
print('New pages deployed:')
print('  http://72.60.140.55:9000/api-docs')
print('  http://72.60.140.55:9000/api-keys')
