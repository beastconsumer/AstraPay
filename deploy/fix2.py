import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('72.60.140.55', 22, 'root', 'Germaninho9949?')

def run(cmd):
    _, stdout, stderr = ssh.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace') + stderr.read().decode('utf-8', errors='replace')

print('=== Create log dir ===')
print(run('mkdir -p /var/log/astrapay && chmod 755 /var/log/astrapay'))

print('=== Check public/index.php ===')
print(run('ls -la /root/astrapay/public/index.php'))

print('=== PHP Syntax Check ===')
print(run('cd /root/astrapay/public && php -l index.php'))

print('=== Kill existing PHP on 9000 ===')
print(run('fuser -k 9000/tcp 2>/dev/null; echo done'))

print('=== Restart Service ===')
print(run('systemctl daemon-reload'))
print(run('systemctl restart astrapay'))
print(run('sleep 3'))
print(run('systemctl is-active astrapay'))

print('=== Test curl ===')
print(run('curl -s -o /dev/null -w "%{http_code}" http://localhost:9000/'))

print('=== Journal ===')
print(run('journalctl -u astrapay --no-pager -n 10 -o cat 2>&1'))

ssh.close()
