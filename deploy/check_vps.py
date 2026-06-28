import paramiko
import os
import sys

host = '72.60.140.55'
port = 22
username = 'root'
password = 'Germaninho9949?'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)

print('=== CHECKING VPS STRUCTURE ===')
_, stdout, _ = ssh.exec_command('find /root/astrapay -type d | sort')
print(stdout.read().decode())

_, stdout, _ = ssh.exec_command('find /root/astrapay -type f -name "*.php" | sort')
print(stdout.read().decode())

print('\n=== CHECKING PUBLIC ASSETS ===')
_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/public/assets/ 2>/dev/null || echo "no public/assets dir"')
print(stdout.read().decode())

_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/public/assets/css/ 2>/dev/null || echo "no css dir"')
print(stdout.read().decode())

_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/public/assets/js/ 2>/dev/null || echo "no js dir"')
print(stdout.read().decode())

_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/public/templates/ 2>/dev/null || echo "no templates dir"')
print(stdout.read().decode())

_, stdout, _ = ssh.exec_command('ls -la /root/astrapay/public/templates/pages/ 2>/dev/null || echo "no pages dir"')
print(stdout.read().decode())

ssh.close()
print('\n=== DONE CHECKING ===')
