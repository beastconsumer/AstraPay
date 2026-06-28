import paramiko, sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8', errors='replace')

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('72.60.140.55', 22, 'root', 'Germaninho9949?')

def run(cmd):
    _, stdout, stderr = ssh.exec_command(cmd)
    return stdout.read().decode('utf-8', errors='replace')

print('=== Landing Page Full Content (first 5000 chars) ===')
content = run('curl -s http://localhost:9000/')
print(content[:5000])

print('\n=== CHECK KEY ELEMENTS ===')
checks = ['logoescrita', 'Infraestrutura', 'glass-icon', 'hero-bg', 'Space Grotesk', 'astra-card', 'Comecar agora', 'Documentacao API']
for c in checks:
    found = c in content
    print(f'  "{c}": {"FOUND" if found else "MISSING"}')

print('\n=== Login Page (first 3000 chars) ===')
content = run('curl -s http://localhost:9000/login')
print(content[:3000])

checks2 = ['logoescrita', 'astra-card', 'astra-input', 'astra-btn', 'login-bg', 'Entrar']
for c in checks2:
    found = c in content
    print(f'  "{c}": {"FOUND" if found else "MISSING"}')

ssh.close()
