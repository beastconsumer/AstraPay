import requests
import json
import random

base = 'http://72.60.140.55:9000'
rand_suffix = random.randint(10000, 99999)
email = f'testuser{rand_suffix}@gmail.com'
name = 'Test User'
password = 'Test1234A'

print('=== FULL FLOW TEST ===')
print()

# 1. Register
print('1. REGISTER')
r = requests.post(f'{base}/api/auth/register', json={
    'name': name, 'email': email, 'password': password,
    'password_confirmation': password
}, timeout=30)
print(f'  Status: {r.status_code}')
data = r.json()
token = data.get('data', {}).get('token')
if not token:
    print(f'  FAILED: {json.dumps(data, indent=2)[:400]}')
    exit()
print(f'  SUCCESS - Token: {token[:30]}...')

# 2. Login
print()
print('2. LOGIN')
r = requests.post(f'{base}/api/auth/login', json={
    'email': email, 'password': password
}, timeout=30)
print(f'  Status: {r.status_code}')
data = r.json()
token = data.get('data', {}).get('token')
if not token:
    print(f'  FAILED: {json.dumps(data, indent=2)[:400]}')
    exit()
print(f'  SUCCESS - New Token: {token[:30]}...')

# 3. Me
print()
print('3. GET /api/auth/me')
r = requests.get(f'{base}/api/auth/me', headers={'Authorization': f'Bearer {token}'}, timeout=15)
data = r.json()
print(f'  Status: {r.status_code}')
user_data = data.get('data', {}).get('user', {})
print(f'  User: {user_data.get("email", "N/A")}')
print(f'  Tier: {user_data.get("tier", "N/A")}')

# 4. Dashboard stats
print()
print('4. GET /api/user/stats')
r = requests.get(f'{base}/api/user/stats', headers={'Authorization': f'Bearer {token}'}, timeout=15)
data = r.json()
print(f'  Status: {r.status_code}')
if data.get('success'):
    stats = data.get('data', {})
    print(f'  Balance: R$ {stats.get("saldo", stats.get("current_balance", 0))}')
    print(f'  Total Received: R$ {stats.get("total_recebido", stats.get("total_received", 0))}')

# 5. Create PIX
print()
print('5. POST /api/pix/create (R$ 10.00)')
r = requests.post(f'{base}/api/pix/create', 
    headers={'Authorization': f'Bearer {token}', 'Content-Type': 'application/json'},
    json={'amount': 10.00, 'description': 'Teste PIX'}, timeout=30)
data = r.json()
print(f'  Status: {r.status_code}')
if data.get('success'):
    tx = data.get('data', {}).get('transaction', data.get('data', {}))
    print(f'  Tx ID: {tx.get("id")}')
    print(f'  Amount: R$ {tx.get("amount", tx.get("valor", 0))}')
    pix_code = tx.get('pix_copy_paste', tx.get('copy_paste', ''))
    if pix_code:
        print(f'  PIX Copy-Paste: {pix_code[:60]}...')
    else:
        print(f'  PIX Code: NOT AVAILABLE (Asaas issue - expected)')
else:
    err = data.get('error', 'unknown')
    msg = data.get('message', '')
    print(f'  PIX creation: {err} - {msg[:200]}')

# 6. API Keys - list
print()
print('6. GET /api/keys/list')
r = requests.get(f'{base}/api/keys/list', headers={'Authorization': f'Bearer {token}'}, timeout=15)
data = r.json()
print(f'  Status: {r.status_code}')
if data.get('success'):
    keys = data.get('data', {}).get('keys', [])
    print(f'  Keys count: {len(keys)}')
else:
    print(f'  Error: {data.get("error", "unknown")}')

# 7. Generate API Key
print()
print('7. POST /api/keys/generate')
r = requests.post(f'{base}/api/keys/generate', 
    headers={'Authorization': f'Bearer {token}', 'Content-Type': 'application/json'},
    json={'name': 'My Test App'}, timeout=15)
data = r.json()
print(f'  Status: {r.status_code}')
api_key = None
if data.get('success'):
    api_key = data.get('data', {}).get('key', {}).get('api_key')
    if api_key:
        print(f'  API Key: {api_key[:30]}...')
    else:
        print(f'  Response: {json.dumps(data, indent=2)[:300]}')
else:
    print(f'  Error: {data.get("error", "unknown")} - {data.get("message", "")[:100]}')

# 8. Test Public API with API Key
if api_key:
    print()
    print('8. Test Public API (X-Api-Key)')
    print('   GET /api/v1/balance...')
    r = requests.get(f'{base}/api/v1/balance', headers={'X-Api-Key': api_key}, timeout=15)
    data = r.json()
    print(f'   Status: {r.status_code}')
    if data.get('success'):
        bal = data.get('data', {}).get('balance', {})
        print(f'   Balance: R$ {bal.get("current", 0)}')
    else:
        print(f'   Error: {data.get("error", "")}: {data.get("message", "")[:100]}')

    print()
    print('   GET /api/v1/transactions...')
    r = requests.get(f'{base}/api/v1/transactions', headers={'X-Api-Key': api_key}, timeout=15)
    data = r.json()
    print(f'   Status: {r.status_code}')
    if data.get('success'):
        txs = data.get('data', {}).get('transactions', [])
        print(f'   Transactions: {len(txs)}')
    else:
        print(f'   Error: {data.get("error", "")}')

print()
print('=== FULL FLOW TEST COMPLETE ===')
print(f'  Test account: {email}')
print(f'  Password: {password}')
