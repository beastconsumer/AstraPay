<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

function ensureApiKeysTable()
{
    $db = DB::getInstance();
    $db->execute("CREATE TABLE IF NOT EXISTS api_keys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        api_key TEXT NOT NULL UNIQUE,
        name TEXT DEFAULT 'Default',
        rate_limit INTEGER DEFAULT 60,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        last_used_at TEXT DEFAULT NULL
    )");
    $db->execute("CREATE INDEX IF NOT EXISTS idx_apikeys_key ON api_keys(api_key)");
    $db->execute("CREATE INDEX IF NOT EXISTS idx_apikeys_user ON api_keys(user_id)");
}

function authenticateUserForKeys()
{
    session_start();
    if (empty($_SESSION['user_id'])) {
        jsonResponse(['success' => false, 'error' => 'Authentication required.'], 401);
    }
    $db = DB::getInstance();
    $user = $db->fetch("SELECT id, email, name, tier, banned FROM users WHERE id = ?", [(int)$_SESSION['user_id']]);
    if (!$user || $user['banned']) { session_destroy(); jsonResponse(['success' => false, 'error' => 'Account not found or banned.'], 403); }
    return $user;
}

function generateApiKeyString() { return 'astrapay_' . bin2hex(random_bytes(32)); }

function maskApiKeyStr($key)
{
    if (strlen($key) <= 10) return str_repeat('*', strlen($key));
    return substr($key, 0, 3) . '...' . substr($key, -4);
}

function handleApiKeysRoutes()
{
    ensureApiKeysTable();
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');
    $method = $_SERVER['REQUEST_METHOD'];
    $pathParts = explode('/', trim($uri, '/'));
    if (count($pathParts) < 2 || $pathParts[0] !== 'api' || $pathParts[1] !== 'keys') {
        jsonResponse(['success' => false, 'error' => 'Not an API keys endpoint.'], 404);
    }

    $user = authenticateUserForKeys();
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if ($method === 'POST' && count($pathParts) === 3 && $pathParts[2] === 'generate') {
        $name = trim($input['name'] ?? 'Default') ?: 'Default';
        $db = DB::getInstance();
        $count = $db->fetch("SELECT COUNT(*) as c FROM api_keys WHERE user_id = ?", [$user['id']])['c'];
        if ($count >= 10) jsonResponse(['success' => false, 'error' => 'Maximum of 10 API keys reached.'], 422);
        $apiKey = generateApiKeyString();
        $db->execute("INSERT INTO api_keys (user_id, api_key, name) VALUES (?, ?, ?)", [$user['id'], $apiKey, $name]);
        $id = $db->lastInsertId();
        jsonResponse(['success' => true, 'data' => ['key' => ['id' => $id, 'name' => $name, 'api_key' => $apiKey, 'created_at' => date('Y-m-d\TH:i:s')], 'warning' => 'Store this key securely. It will never be shown again.']], 201);
    }

    if ($method === 'GET' && count($pathParts) === 3 && $pathParts[2] === 'list') {
        $db = DB::getInstance();
        $keys = $db->fetchAll("SELECT id, name, api_key, rate_limit, is_active, created_at, last_used_at FROM api_keys WHERE user_id = ? ORDER BY id DESC", [$user['id']]);
        $result = array_map(function ($k) {
            return ['id' => $k['id'], 'name' => $k['name'], 'api_key_masked' => maskApiKeyStr($k['api_key']),
                'rate_limit' => (int)$k['rate_limit'], 'is_active' => (bool)$k['is_active'],
                'created_at' => $k['created_at'], 'last_used_at' => $k['last_used_at']];
        }, $keys);
        jsonResponse(['success' => true, 'data' => ['keys' => $result]]);
    }

    if ($method === 'POST' && count($pathParts) === 4 && $pathParts[2] === 'revoke') {
        $db = DB::getInstance();
        $key = $db->fetch("SELECT * FROM api_keys WHERE id = ? AND user_id = ?", [(int)$pathParts[3], $user['id']]);
        if (!$key) jsonResponse(['success' => false, 'error' => 'API key not found.'], 404);
        $db->execute("UPDATE api_keys SET is_active = 0 WHERE id = ?", [$key['id']]);
        jsonResponse(['success' => true, 'data' => ['message' => 'API key revoked successfully.']]);
    }

    if ($method === 'POST' && count($pathParts) === 4 && $pathParts[2] === 'rotate') {
        $db = DB::getInstance();
        $key = $db->fetch("SELECT * FROM api_keys WHERE id = ? AND user_id = ?", [(int)$pathParts[3], $user['id']]);
        if (!$key) jsonResponse(['success' => false, 'error' => 'API key not found.'], 404);
        $newKey = generateApiKeyString();
        $db->execute("UPDATE api_keys SET api_key = ?, is_active = 1, created_at = datetime('now'), last_used_at = NULL WHERE id = ?", [$newKey, $key['id']]);
        jsonResponse(['success' => true, 'data' => ['key' => ['id' => $key['id'], 'name' => $key['name'], 'api_key' => $newKey, 'created_at' => date('Y-m-d\TH:i:s')], 'warning' => 'Store this key securely. The old key has been deactivated.']]);
    }

    jsonResponse(['success' => false, 'error' => 'Endpoint not found.'], 404);
}
