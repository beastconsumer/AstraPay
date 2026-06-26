<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

function ensureApiKeysTablePublic()
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

function authenticateApiKey()
{
    $headers = getallheaders();
    $apiKey = null;
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'x-api-key') { $apiKey = $value; break; }
    }
    if (!$apiKey) {
        jsonResponse(['success' => false, 'error' => 'API key required. Send via X-Api-Key header.'], 401);
    }

    ensureApiKeysTablePublic();
    $db = DB::getInstance();
    $key = $db->fetch(
        "SELECT ak.*, u.id as uid, u.email, u.name, u.tier, u.daily_limit, u.monthly_limit,
        u.per_tx_limit, u.admin_fee_pct, u.current_balance, u.banned
        FROM api_keys ak JOIN users u ON ak.user_id = u.id
        WHERE ak.api_key = ? AND ak.is_active = 1", [$apiKey]
    );

    if (!$key) { jsonResponse(['success' => false, 'error' => 'Invalid API key.'], 401); }
    if ($key['banned']) { jsonResponse(['success' => false, 'error' => 'Account banned.'], 403); }

    $db->execute("UPDATE api_keys SET last_used_at = datetime('now') WHERE id = ?", [$key['id']]);
    return $key;
}

function checkRateLimitPublic($keyId, $rateLimitPerMinute = 60)
{
    $rateDir = __DIR__ . '/storage';
    if (!is_dir($rateDir)) mkdir($rateDir, 0775, true);
    $rateFile = $rateDir . '/ratelimit_' . $keyId . '.json';
    $now = time(); $window = 60;
    $data = ['timestamps' => []];
    if (file_exists($rateFile)) {
        $data = json_decode(file_get_contents($rateFile), true) ?: $data;
    }
    $data['timestamps'] = array_filter($data['timestamps'], fn($ts) => ($now - $ts) < $window);
    if (count($data['timestamps']) >= $rateLimitPerMinute) {
        $oldest = min($data['timestamps']);
        $retryAfter = $oldest + $window - $now;
        header('Retry-After: ' . $retryAfter);
        jsonResponse(['success' => false, 'error' => 'Rate limit exceeded. Try again in ' . $retryAfter . ' seconds.'], 429);
    }
    $data['timestamps'][] = $now;
    @file_put_contents($rateFile, json_encode($data), LOCK_EX);
    return true;
}

function callAsaasApiPublic($method, $endpoint, $body = null)
{
    $url = ASAAS_API_URL . $endpoint;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'access_token: ' . ASAAS_API_KEY,
        ],
    ]);
    if ($method === 'POST') { curl_setopt($ch, CURLOPT_POST, true); if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body)); }
    elseif ($method === 'GET' && $body) { curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($body)); }

    $response = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
    if ($error) { jsonResponse(['success' => false, 'error' => 'Asaas API error: ' . $error], 503); }
    $data = json_decode($response, true);
    if ($httpCode >= 400) {
        $msg = $data['errors'][0]['description'] ?? ($data['message'] ?? 'Asaas API error');
        jsonResponse(['success' => false, 'error' => $msg], 502);
    }
    return $data;
}

function getUserLimitsPublic($user)
{
    $db = DB::getInstance();
    $daily = $db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND status NOT IN ('cancelled', 'refunded') AND date(created_at) = date('now')", [$user['uid']]);
    $monthly = $db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND status NOT IN ('cancelled', 'refunded') AND strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')", [$user['uid']]);
    return [
        'daily_used' => (float)($daily['total'] ?? 0),
        'daily_limit' => (float)$user['daily_limit'],
        'monthly_used' => (float)($monthly['total'] ?? 0),
        'monthly_limit' => (float)$user['monthly_limit'],
        'per_tx_limit' => (float)$user['per_tx_limit'],
        'admin_fee_pct' => (float)$user['admin_fee_pct'],
    ];
}

function publicApiCreatePix($keyUser, $input)
{
    checkRateLimitPublic($keyUser['id'], (int)$keyUser['rate_limit']);
    $valor = isset($input['valor']) ? (float)$input['valor'] : 0;
    $descricao = isset($input['descricao']) ? trim($input['descricao']) : '';
    if ($valor <= 0) jsonResponse(['success' => false, 'error' => 'Field "valor" is required and must be > 0.'], 422);

    $limits = getUserLimitsPublic($keyUser);
    if ($valor > $limits['per_tx_limit']) jsonResponse(['success' => false, 'error' => "Amount exceeds per-transaction limit of R$ " . number_format($limits['per_tx_limit'], 2, ',', '.')], 422);
    if (($limits['daily_used'] + $valor) > $limits['daily_limit']) jsonResponse(['success' => false, 'error' => "Daily limit would be exceeded."], 422);
    if (($limits['monthly_used'] + $valor) > $limits['monthly_limit']) jsonResponse(['success' => false, 'error' => "Monthly limit would be exceeded."], 422);

    $feePct = $limits['admin_fee_pct'];
    $feeAmount = round($valor * ($feePct / 100), 2);
    $netAmount = round($valor - $feeAmount, 2);
    $asaasBody = [
        'billingType' => 'PIX', 'value' => $valor, 'dueDate' => date('Y-m-d'),
        'description' => $descricao ?: 'Pagamento via AstraPay',
        'externalReference' => 'astrapay_api_' . $keyUser['uid'] . '_' . time(),
        'postalService' => false,
    ];
    if (!empty($input['payer_name'])) $asaasBody['customer'] = $input['payer_name'];
    if (!empty($input['payer_cpf_cnpj'])) {
        $cpfCnpj = preg_replace('/[^0-9]/', '', $input['payer_cpf_cnpj']);
        $asaasBody['customer'] = $asaasBody['customer'] ?? $cpfCnpj;
        if (strlen($cpfCnpj) <= 11) $asaasBody['cpfCnpj'] = $cpfCnpj;
    }
    $result = callAsaasApiPublic('POST', '/payments', $asaasBody);
    $expiration = null;
    if (!empty($result['dueDate'])) $expiration = date('Y-m-d\TH:i:s', strtotime($result['dueDate'] . ' +30 minutes'));

    $db = DB::getInstance();
    $db->execute("INSERT INTO transactions (user_id, asaas_payment_id, external_ref, amount, net_amount, fee_amount, fee_percent, status, description, payer_name, payer_cpf_cnpj, payer_email, pix_copy_paste, pix_qrcode_url, pix_expiration, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))", [
        $keyUser['uid'], $result['id'] ?? null, $asaasBody['externalReference'], $valor, $netAmount, $feeAmount, $feePct,
        $descricao, $input['payer_name'] ?? null, $input['payer_cpf_cnpj'] ?? null, $input['payer_email'] ?? null,
        $result['pixQrCode']['payload'] ?? ($result['payload'] ?? null),
        $result['pixQrCode']['encodedImage'] ?? ($result['pixQrCodeUrl'] ?? null), $expiration,
    ]);
    $txId = $db->lastInsertId();
    jsonResponse(['success' => true, 'data' => ['transaction' => [
        'id' => $txId, 'amount' => $valor, 'net_amount' => $netAmount, 'fee_amount' => $feeAmount,
        'fee_percent' => $feePct, 'status' => 'pending',
        'pix_copy_paste' => $result['pixQrCode']['payload'] ?? ($result['payload'] ?? null),
        'pix_qrcode_url' => $result['pixQrCode']['encodedImage'] ?? ($result['pixQrCodeUrl'] ?? null),
        'pix_expiration' => $expiration, 'description' => $descricao,
    ]]], 201);
}

function publicApiCheckPix($keyUser, $txId)
{
    $db = DB::getInstance();
    $tx = $db->fetch("SELECT * FROM transactions WHERE id = ? AND user_id = ?", [(int)$txId, $keyUser['uid']]);
    if (!$tx) jsonResponse(['success' => false, 'error' => 'Transaction not found.'], 404);
    jsonResponse(['success' => true, 'data' => ['transaction' => [
        'id' => $tx['id'], 'amount' => (float)$tx['amount'], 'net_amount' => (float)$tx['net_amount'],
        'fee_amount' => (float)$tx['fee_amount'], 'fee_percent' => (float)$tx['fee_percent'],
        'status' => $tx['status'], 'pix_copy_paste' => $tx['pix_copy_paste'],
        'pix_qrcode_url' => $tx['pix_qrcode_url'], 'pix_expiration' => $tx['pix_expiration'],
        'description' => $tx['description'], 'payer_name' => $tx['payer_name'],
        'payer_cpf_cnpj' => $tx['payer_cpf_cnpj'], 'created_at' => $tx['created_at'], 'updated_at' => $tx['updated_at'],
    ]]]);
}

function publicApiGetBalance($keyUser)
{
    $db = DB::getInstance();
    $user = $db->fetch("SELECT current_balance, total_received, total_withdrawn FROM users WHERE id = ?", [$keyUser['uid']]);
    jsonResponse(['success' => true, 'data' => ['balance' => [
        'current' => (float)$user['current_balance'], 'total_received' => (float)$user['total_received'],
        'total_withdrawn' => (float)$user['total_withdrawn'],
    ]]]);
}

function publicApiListTransactions($keyUser)
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
    $db = DB::getInstance();
    $where = "WHERE user_id = ?"; $params = [$keyUser['uid']];
    if ($statusFilter) { $where .= " AND status = ?"; $params[] = $statusFilter; }
    $total = $db->fetch("SELECT COUNT(*) as c FROM transactions " . $where, $params)['c'];
    $txs = $db->fetchAll("SELECT id, amount, net_amount, fee_amount, fee_percent, status, description, payer_name, pix_copy_paste, pix_qrcode_url, created_at, updated_at FROM transactions " . $where . " ORDER BY id DESC LIMIT ? OFFSET ?", array_merge($params, [$limit, $offset]));
    jsonResponse(['success' => true, 'data' => [
        'transactions' => array_map(function ($t) {
            return ['id' => $t['id'], 'amount' => (float)$t['amount'], 'net_amount' => (float)$t['net_amount'],
                'fee_amount' => (float)$t['fee_amount'], 'fee_percent' => (float)$t['fee_percent'],
                'status' => $t['status'], 'description' => $t['description'], 'payer_name' => $t['payer_name'],
                'pix_copy_paste' => $t['pix_copy_paste'], 'created_at' => $t['created_at'], 'updated_at' => $t['updated_at']];
        }, $txs),
        'pagination' => ['page' => $page, 'limit' => $limit, 'total' => (int)$total, 'total_pages' => (int)ceil($total / $limit)],
    ]]);
}

function handlePublicApi()
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');
    $method = $_SERVER['REQUEST_METHOD'];
    $pathParts = explode('/', trim($uri, '/'));
    if (count($pathParts) < 3 || $pathParts[0] !== 'api' || $pathParts[1] !== 'v1') {
        jsonResponse(['success' => false, 'error' => 'Use /api/v1/... endpoints.'], 404);
    }
    $keyUser = authenticateApiKey();
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if ($method === 'POST' && count($pathParts) === 3 && $pathParts[2] === 'pix') {
        publicApiCreatePix($keyUser, $input);
    } elseif ($method === 'GET' && count($pathParts) === 4 && $pathParts[2] === 'pix') {
        checkRateLimitPublic($keyUser['id'], (int)$keyUser['rate_limit']);
        publicApiCheckPix($keyUser, $pathParts[3]);
    } elseif ($method === 'GET' && count($pathParts) === 3 && $pathParts[2] === 'balance') {
        checkRateLimitPublic($keyUser['id'], (int)$keyUser['rate_limit']);
        publicApiGetBalance($keyUser);
    } elseif ($method === 'GET' && count($pathParts) === 3 && $pathParts[2] === 'transactions') {
        checkRateLimitPublic($keyUser['id'], (int)$keyUser['rate_limit']);
        publicApiListTransactions($keyUser);
    } else {
        jsonResponse(['success' => false, 'error' => 'Endpoint not found.'], 404);
    }
}
