import subprocess
import sys

host = '72.60.140.55'
port = 22
username = 'root'
password = 'Germaninho9949?'

import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)

def run(cmd):
    _, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    err = stderr.read().decode('utf-8', errors='replace')
    return out + err

# Restart service
print(run('systemctl restart astrapay 2>&1'))
print(run('sleep 2 && systemctl is-active astrapay 2>&1'))

# Verify endpoints
print('\n--- HTTP Status Codes ---')
for path in ['/', '/login', '/register', '/dashboard', '/pix', '/transactions', '/settings', '/api-docs', '/api-keys', '/verify-email']:
    code = run(f'curl -s -o /dev/null -w "%{{http_code}}" http://localhost:9000{path} 2>&1')
    print(f'  {path}: {code.strip()}')

print('\n--- Static Assets ---')
for path in ['/assets/css/app.css', '/assets/js/app.js', '/assets/logobola.png', '/assets/logoescrita.png', '/assets/hero-bg.mp4', '/assets/login-bg.mp4']:
    code = run(f'curl -s -o /dev/null -w "%{{http_code}}" http://localhost:9000{path} 2>&1')
    print(f'  {path}: {code.strip()}')

print('\n--- Login Test ---')
result = run('curl -s -X POST http://localhost:9000/api/auth/login -H "Content-Type: application/json" -d \'{"email":"bbb@gmail.com","password":"fada123"}\' 2>&1')
print(result[:800])

ssh.close()
print('\nAll done!')
