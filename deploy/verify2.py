import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('72.60.140.55', 22, 'root', 'Germaninho9949?')

def run(cmd):
    _, stdout, stderr = ssh.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace').strip()

# Fix log dir issue permanently
print('=== Fix Log Dir ===')
print(run('mkdir -p /var/log/astrapay && echo exists'))

# Now verify all endpoints
print('\n=== Page HTTP Codes ===')
pages = ['/', '/login', '/register', '/dashboard', '/pix', '/transactions', '/settings', '/api-docs', '/api-keys', '/verify-email']
for p in pages:
    code = run(f'curl -s -o /dev/null -w "%{{http_code}}" http://localhost:9000{p}')
    print(f'  {p}: {code}')

print('\n=== Static Assets ===')
assets = ['/assets/css/app.css', '/assets/js/app.js', '/assets/logobola.png', '/assets/logoescrita.png', '/assets/hero-bg.mp4', '/assets/login-bg.mp4']
for a in assets:
    code = run(f'curl -s -o /dev/null -w "%{{http_code}}" http://localhost:9000{a}')
    print(f'  {a}: {code}')

print('\n=== Login API Test ===')
result = run("""curl -s -X POST http://localhost:9000/api/auth/login -H 'Content-Type: application/json' -d '{"email":"bbb@gmail.com","password":"fada123"}'""")
print(result[:600])

print('\n=== Landing Page Title Check ===')
content = run('curl -s http://localhost:9000/ 2>&1 | head -30')
# Check for key elements
checks = ['Space+Grotesk', 'logoescrita', 'Infraestrutura', 'glass-icon', 'hero-bg']
for c in checks:
    found = c.lower() in content.lower()
    print(f'  Contains "{c}": {found}')

print('\n=== Login Page Check ===')
content = run('curl -s http://localhost:9000/login 2>&1 | head -20')
for c in ['login-bg', 'logoescrita', 'astra-card', 'astra-input', 'astra-btn']:
    found = c.lower() in content.lower()
    print(f'  Contains "{c}": {found}')

# Final restart to fix logs
run('systemctl restart astrapay')
print('\n=== Service Active: ' + run('systemctl is-active astrapay') + ' ===')

ssh.close()
