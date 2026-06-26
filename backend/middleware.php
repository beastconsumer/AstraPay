<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}

function generateToken(): string
{
    return bin2hex(random_bytes(32));
}

function jsonResponse(array $body, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function getClientIP(): string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function getClientUA(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

function auth(): array
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
        jsonResponse([
            'success' => false,
            'error' => 'unauthorized',
            'message' => 'Token de autenticacao nao fornecido'
        ], 401);
    }

    $token = $m[1];
    $db = DB::getInstance();

    $user = $db->fetch(
        "SELECT u.* FROM session_tokens st
         JOIN users u ON st.user_id = u.id
         WHERE st.token = ? AND st.expires_at > datetime('now')",
        [$token]
    );

    if (!$user) {
        jsonResponse([
            'success' => false,
            'error' => 'unauthorized',
            'message' => 'Token invalido ou expirado'
        ], 401);
    }

    if ($user['banned']) {
        jsonResponse([
            'success' => false,
            'error' => 'banned',
            'message' => 'Conta suspensa: ' . ($user['ban_reason'] ?: 'Violacao dos termos de uso')
        ], 423);
    }

    return $user;
}

function admin(): array
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $m)) {
        jsonResponse([
            'success' => false,
            'error' => 'unauthorized',
            'message' => 'Token de autenticacao nao fornecido'
        ], 401);
    }

    $token = $m[1];
    $db = DB::getInstance();

    $admin = $db->fetch(
        "SELECT au.* FROM session_tokens st
         JOIN admin_users au ON st.admin_user_id = au.id
         WHERE st.token = ? AND st.expires_at > datetime('now')",
        [$token]
    );

    if (!$admin) {
        jsonResponse([
            'success' => false,
            'error' => 'forbidden',
            'message' => 'Acesso restrito a administradores'
        ], 403);
    }

    if (!$admin['is_active']) {
        jsonResponse([
            'success' => false,
            'error' => 'forbidden',
            'message' => 'Conta de administrador desativada'
        ], 403);
    }

    if ($admin['ip_whitelist'] !== null) {
        $allowed = json_decode($admin['ip_whitelist'], true);
        if (is_array($allowed) && !ipInRange(getClientIP(), $allowed)) {
            jsonResponse([
                'success' => false,
                'error' => 'forbidden',
                'message' => 'IP nao autorizado para este administrador'
            ], 403);
        }
    }

    return $admin;
}

function ipInRange(string $ip, array $ranges): bool
{
    foreach ($ranges as $range) {
        if ($ip === $range) {
            return true;
        }
        if (strpos($range, '/') !== false) {
            list($subnet, $bits) = explode('/', $range);
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            if ($ipLong === false || $subnetLong === false) continue;
            $mask = -1 << (32 - (int)$bits);
            if (($ipLong & $mask) === ($subnetLong & $mask)) {
                return true;
            }
        }
    }
    return false;
}

function rateLimit(string $key, int $max, int $windowSeconds): bool
{
    $file = DATA_DIR . '/rate_limits.json';
    $attempts = [];

    if (file_exists($file)) {
        $raw = file_get_contents($file);
        $attempts = json_decode($raw, true) ?? [];
    }

    $now = time();

    if (!isset($attempts[$key])) {
        $attempts[$key] = [];
    }

    $attempts[$key] = array_values(
        array_filter($attempts[$key], function ($t) use ($now, $windowSeconds) {
            return $t > ($now - $windowSeconds);
        })
    );

    if (count($attempts[$key]) >= $max) {
        return false;
    }

    $attempts[$key][] = $now;
    file_put_contents($file, json_encode($attempts), LOCK_EX);

    return true;
}

function validateCPF(string $cpf): array|string
{
    $clean = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($clean) !== 11) {
        return ['valid' => false, 'error' => 'CPF deve ter 11 digitos'];
    }

    if (preg_match('/^(\d)\1{10}$/', $clean)) {
        return ['valid' => false, 'error' => 'CPF invalido (todos os digitos iguais)'];
    }

    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += intval($clean[$i]) * (10 - $i);
    }
    $remainder = $sum % 11;
    $digit1 = ($remainder < 2) ? 0 : (11 - $remainder);

    if ($digit1 != intval($clean[9])) {
        return ['valid' => false, 'error' => 'CPF invalido (digito verificador 1)'];
    }

    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $sum += intval($clean[$i]) * (11 - $i);
    }
    $remainder = $sum % 11;
    $digit2 = ($remainder < 2) ? 0 : (11 - $remainder);

    if ($digit2 != intval($clean[10])) {
        return ['valid' => false, 'error' => 'CPF invalido (digito verificador 2)'];
    }

    $brasilApiResult = callBrasilAPI($clean);

    if ($brasilApiResult === null) {
        $cleanFormatted = substr($clean, 0, 3) . '.' . substr($clean, 3, 3) . '.' . substr($clean, 6, 3) . '-' . substr($clean, 9, 2);
        return [
            'valid' => true,
            'clean' => $clean,
            'formatted' => $cleanFormatted,
            'brasilapi_verified' => false,
            'warning' => 'BrasilAPI indisponivel. CPF aceito com validacao matematica.'
        ];
    }

    $status = $brasilApiResult['status'] ?? '';
    if ($status !== 'REGULAR') {
        return ['valid' => false, 'error' => 'CPF nao esta regular na Receita Federal (status: ' . $status . ')'];
    }

    $cleanFormatted = substr($clean, 0, 3) . '.' . substr($clean, 3, 3) . '.' . substr($clean, 6, 3) . '-' . substr($clean, 9, 2);
    return [
        'valid' => true,
        'clean' => $clean,
        'formatted' => $cleanFormatted,
        'brasilapi_verified' => true,
        'name' => $brasilApiResult['name'] ?? null
    ];
}

function callBrasilAPI(string $cpfClean): ?array
{
    $cacheFile = DATA_DIR . '/cpf_cache.json';
    $cache = [];
    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true) ?? [];
    }

    if (isset($cache[$cpfClean])) {
        $entry = $cache[$cpfClean];
        if (time() - $entry['cached_at'] < 86400) {
            return $entry['data'];
        }
    }

    $ch = curl_init('https://brasilapi.com.br/api/cpf/v1/' . $cpfClean);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        $cache[$cpfClean] = ['data' => $data, 'cached_at' => time()];
        file_put_contents($cacheFile, json_encode($cache), LOCK_EX);
        return $data;
    }

    return null;
}

function csrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCheck(?string $token = null): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($token === null) {
        $headers = getallheaders();
        $token = $headers['X-Csrf-Token'] ?? ($_POST['csrf_token'] ?? ($_GET['csrf_token'] ?? ''));
    }

    $stored = $_SESSION['csrf_token'] ?? '';

    if (empty($stored) || empty($token)) {
        return false;
    }

    return hash_equals($stored, $token);
}

function logAudit(?int $userId, ?int $adminId, string $action, ?string $entityType = null, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null, ?array $metadata = null): void
{
    try {
        $db = DB::getInstance();
        $db->execute(
            "INSERT INTO audit_log (user_id, admin_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, metadata)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $adminId,
                $action,
                $entityType,
                $entityId,
                $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                getClientIP(),
                getClientUA(),
                $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            ]
        );
    } catch (Exception $e) {
        error_log('AstraPay audit_log error: ' . $e->getMessage());
    }
}

function recordLoginAttempt(string $ip, ?string $email, string $type, bool $success): void
{
    try {
        $db = DB::getInstance();
        $db->execute(
            "INSERT INTO login_attempts (ip_address, email, type, success) VALUES (?, ?, ?, ?)",
            [$ip, $email, $type, $success ? 1 : 0]
        );
    } catch (Exception $e) {
        error_log('AstraPay login_attempts error: ' . $e->getMessage());
    }
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
