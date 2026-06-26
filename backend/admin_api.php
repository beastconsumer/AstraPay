<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/admin_auth.php';

function handleAdminApi(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Csrf-Token');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');
    $method = $_SERVER['REQUEST_METHOD'];

    if ($uri === '/api/admin/login' && $method === 'POST') {
        handleAdminLogin();
        return;
    }

    if ($uri === '/api/admin/logout' && $method === 'POST') {
        adminLogout();
        jsonResponse(['success' => true, 'message' => 'Logged out']);
        return;
    }

    $adminUser = admin();

    if ($uri === '/api/admin/me' && $method === 'GET') {
        jsonResponse(['success' => true, 'data' => [
            'id' => (int)$adminUser['id'],
            'username' => $adminUser['username'],
            'role' => $adminUser['role'],
        ]]);
        return;
    }

    if ($uri === '/api/admin/stats' && $method === 'GET') {
        handleStats($adminUser);
        return;
    }

    if ($uri === '/api/admin/users' && $method === 'GET') {
        handleUsersList();
        return;
    }

    if (preg_match('#^/api/admin/users/(\d+)$#', $uri, $m) && $method === 'GET') {
        handleUserDetail((int)$m[1]);
        return;
    }

    if (preg_match('#^/api/admin/users/(\d+)/ban$#', $uri, $m) && $method === 'POST') {
        handleBanUser((int)$m[1], $adminUser);
        return;
    }

    if (preg_match('#^/api/admin/users/(\d+)/unban$#', $uri, $m) && $method === 'POST') {
        handleUnbanUser((int)$m[1], $adminUser);
        return;
    }

    if (preg_match('#^/api/admin/users/(\d+)/tier$#', $uri, $m) && $method === 'POST') {
        handleChangeTier((int)$m[1], $adminUser);
        return;
    }

    if (preg_match('#^/api/admin/users/(\d+)/limits$#', $uri, $m) && $method === 'POST') {
        handleUpdateLimits((int)$m[1], $adminUser);
        return;
    }

    if (preg_match('#^/api/admin/transactions/(\d+)/review$#', $uri, $m) && $method === 'POST') {
        handleReviewTx((int)$m[1], $adminUser);
        return;
    }

    if ($uri === '/api/admin/transactions' && $method === 'GET') {
        handleTransactionsList();
        return;
    }

    if ($uri === '/api/admin/audit-log' && $method === 'GET') {
        handleAuditLog();
        return;
    }

    if ($uri === '/api/admin/config' && $method === 'GET') {
        handleGetConfig();
        return;
    }

    if ($uri === '/api/admin/config' && $method === 'POST') {
        handleUpdateConfig($adminUser);
        return;
    }

    jsonResponse(['success' => false, 'error' => 'not_found', 'message' => 'Endpoint not found'], 404);
}

function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function handleAdminLogin(): void
{
    $input = getJsonBody();
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        jsonResponse(['success' => false, 'error' => 'validation_error', 'message' => 'Username and password required'], 422);
    }

    $result = adminLogin($username, $password);
    if (!$result['success']) {
        jsonResponse($result, 401);
    }
    jsonResponse($result);
}

function handleStats(array $adminUser): void
{
    $db = DB::getInstance();

    $totalUsers = $db->fetch("SELECT COUNT(*) as c FROM users")['c'];
    $activeToday = $db->fetch("SELECT COUNT(*) as c FROM users WHERE last_login_at >= datetime('now', '-1 day')")['c'];
    $totalTx = $db->fetch("SELECT COUNT(*) as c FROM transactions")['c'];
    $totalVolume = $db->fetch("SELECT COALESCE(SUM(amount), 0) as s FROM transactions WHERE status IN ('confirmed','received')")['s'];
    $totalVolume30d = $db->fetch("SELECT COALESCE(SUM(amount), 0) as s FROM transactions WHERE status IN ('confirmed','received') AND created_at >= datetime('now', '-30 days')")['s'];
    $totalFees30d = $db->fetch("SELECT COALESCE(SUM(fee_amount), 0) as s FROM transactions WHERE status IN ('confirmed','received') AND created_at >= datetime('now', '-30 days')")['s'];
    $pendingWd = $db->fetch("SELECT COUNT(*) as c FROM withdrawals WHERE status IN ('pending','processing')")['c'];
    $heldTx = $db->fetch("SELECT COUNT(*) as c FROM transactions WHERE held = 1 AND status = 'held'")['c'];
    $bannedUsers = $db->fetch("SELECT COUNT(*) as c FROM users WHERE banned = 1")['c'];

    $tiers = $db->fetchAll("SELECT tier, COUNT(*) as c FROM users GROUP BY tier");
    $tierMap = ['new' => 0, 'basic' => 0, 'bronze' => 0, 'silver' => 0, 'gold' => 0];
    foreach ($tiers as $t) { $tierMap[$t['tier']] = (int)$t['c']; }

    $dailyVolume = $db->fetchAll("
        SELECT date(created_at) as dt, COALESCE(SUM(amount),0) as amt, COUNT(*) as cnt
        FROM transactions WHERE status IN ('confirmed','received') AND created_at >= datetime('now', '-7 days')
        GROUP BY date(created_at) ORDER BY dt ASC
    ");

    $recentUsers = $db->fetchAll("SELECT id, name, email, tier, created_at FROM users ORDER BY created_at DESC LIMIT 5");

    jsonResponse([
        'success' => true,
        'data' => [
            'total_users' => (int)$totalUsers,
            'active_today' => (int)$activeToday,
            'total_transactions' => (int)$totalTx,
            'total_volume' => (float)$totalVolume,
            'total_volume_30d' => (float)$totalVolume30d,
            'total_fees_30d' => (float)$totalFees30d,
            'pending_withdrawals' => (int)$pendingWd,
            'held_transactions' => (int)$heldTx,
            'banned_users' => (int)$bannedUsers,
            'users_by_tier' => $tierMap,
            'daily_volume' => $dailyVolume,
            'recent_users' => $recentUsers,
        ]
    ]);
}

function handleUsersList(): void
{
    $db = DB::getInstance();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');
    $tier = trim($_GET['tier'] ?? '');
    $banned = $_GET['banned'] ?? '';

    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(name LIKE ? OR email LIKE ? OR cpf LIKE ?)";
        array_push($params, "%{$search}%", "%{$search}%", "%{$search}%");
    }
    if (!empty($tier) && in_array($tier, ['new','basic','bronze','silver','gold'])) {
        $where[] = "tier = ?";
        $params[] = $tier;
    }
    if ($banned !== '' && in_array($banned, ['0','1'])) {
        $where[] = "banned = ?";
        $params[] = (int)$banned;
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = (int)$db->fetch("SELECT COUNT(*) as c FROM users {$whereClause}", $params)['c'];

    $allParams = array_merge($params, [$limit, $offset]);
    $users = $db->fetchAll("SELECT id, email, name, cpf, tier, current_balance, total_received, banned, ban_reason, email_verified, daily_limit, monthly_limit, per_tx_limit, admin_fee_pct, last_login_at, created_at FROM users {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?", $allParams);

    jsonResponse([
        'success' => true,
        'data' => $users,
        'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => max(1, (int)ceil($total / $limit))],
    ]);
}

function handleUserDetail(int $userId): void
{
    $db = DB::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$user) jsonResponse(['success' => false, 'error' => 'not_found', 'message' => 'User not found'], 404);

    $txCount = $db->fetch("SELECT COUNT(*) as c FROM transactions WHERE user_id = ?", [$userId])['c'];
    $txVolume = $db->fetch("SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE user_id = ? AND status IN ('confirmed','received')", [$userId])['s'];
    $recentTx = $db->fetchAll("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20", [$userId]);
    $recentWd = $db->fetchAll("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 20", [$userId]);
    $audit = $db->fetchAll("SELECT * FROM audit_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 20", [$userId]);

    jsonResponse([
        'success' => true,
        'data' => [
            'user' => $user,
            'stats' => ['total_transactions' => (int)$txCount, 'total_volume' => (float)$txVolume],
            'recent_transactions' => $recentTx,
            'recent_withdrawals' => $recentWd,
            'audit_log' => $audit,
        ]
    ]);
}

function handleBanUser(int $userId, array $adminUser): void
{
    $db = DB::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$user) jsonResponse(['success' => false, 'error' => 'not_found'], 404);
    if ($user['banned']) jsonResponse(['success' => false, 'error' => 'already_banned'], 409);

    $input = getJsonBody();
    $reason = trim($input['reason'] ?? 'Banned by admin');

    $db->execute("UPDATE users SET banned = 1, ban_reason = ?, updated_at = datetime('now') WHERE id = ?", [$reason, $userId]);
    logAudit($userId, (int)$adminUser['id'], 'user.banned', 'users', $userId, ['banned' => 0], ['banned' => 1, 'ban_reason' => $reason]);

    jsonResponse(['success' => true, 'data' => ['id' => $userId, 'banned' => true, 'ban_reason' => $reason]]);
}

function handleUnbanUser(int $userId, array $adminUser): void
{
    $db = DB::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$user) jsonResponse(['success' => false, 'error' => 'not_found'], 404);
    if (!$user['banned']) jsonResponse(['success' => false, 'error' => 'not_banned'], 409);

    $db->execute("UPDATE users SET banned = 0, ban_reason = NULL, updated_at = datetime('now') WHERE id = ?", [$userId]);
    logAudit($userId, (int)$adminUser['id'], 'user.unbanned', 'users', $userId, ['banned' => 1], ['banned' => 0]);

    jsonResponse(['success' => true, 'data' => ['id' => $userId, 'banned' => false]]);
}

function handleChangeTier(int $userId, array $adminUser): void
{
    $input = getJsonBody();
    $tier = trim($input['tier'] ?? '');
    $valid = ['new','basic','bronze','silver','gold'];
    if (!in_array($tier, $valid)) jsonResponse(['success' => false, 'error' => 'validation_error', 'message' => 'Invalid tier'], 422);

    $db = DB::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$user) jsonResponse(['success' => false, 'error' => 'not_found'], 404);

    $oldTier = $user['tier'];

    $limits = [
        'new' => ['daily' => 100, 'monthly' => 500, 'per_tx' => 100, 'admin_fee' => 0],
        'basic' => ['daily' => 1000, 'monthly' => 5000, 'per_tx' => 500, 'admin_fee' => 0],
        'bronze' => ['daily' => 5000, 'monthly' => 25000, 'per_tx' => 2000, 'admin_fee' => 1],
        'silver' => ['daily' => 20000, 'monthly' => 100000, 'per_tx' => 10000, 'admin_fee' => 0.5],
        'gold' => ['daily' => 50000, 'monthly' => 250000, 'per_tx' => 25000, 'admin_fee' => 0],
    ];
    $l = $limits[$tier];

    $db->execute(
        "UPDATE users SET tier = ?, daily_limit = ?, monthly_limit = ?, per_tx_limit = ?, admin_fee_pct = ?, updated_at = datetime('now') WHERE id = ?",
        [$tier, $l['daily'], $l['monthly'], $l['per_tx'], $l['admin_fee'], $userId]
    );

    logAudit($userId, (int)$adminUser['id'], 'user.tier_changed', 'users', $userId, ['tier' => $oldTier], ['tier' => $tier]);

    jsonResponse(['success' => true, 'data' => ['id' => $userId, 'tier' => $tier]]);
}

function handleUpdateLimits(int $userId, array $adminUser): void
{
    $db = DB::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    if (!$user) jsonResponse(['success' => false, 'error' => 'not_found'], 404);

    $input = getJsonBody();
    $fields = [];
    $params = [];
    $oldValues = [];
    $newValues = [];

    $allowed = ['daily_limit', 'monthly_limit', 'per_tx_limit', 'admin_fee_pct'];
    foreach ($allowed as $f) {
        if (isset($input[$f])) {
            $val = round((float)$input[$f], 2);
            $fields[] = "{$f} = ?";
            $params[] = $val;
            $oldValues[$f] = $user[$f];
            $newValues[$f] = $val;
        }
    }

    if (isset($input['tier']) && in_array($input['tier'], ['new','basic','bronze','silver','gold'])) {
        $tier = $input['tier'];
        $fields[] = "tier = ?";
        $params[] = $tier;
        $oldValues['tier'] = $user['tier'];
        $newValues['tier'] = $tier;
    }

    if (empty($fields)) jsonResponse(['success' => false, 'error' => 'validation_error', 'message' => 'No fields'], 422);

    $fields[] = "updated_at = datetime('now')";
    $params[] = $userId;

    $db->execute("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?", $params);

    logAudit($userId, (int)$adminUser['id'], 'user.limits_changed', 'users', $userId, $oldValues, $newValues);

    $updated = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
    jsonResponse(['success' => true, 'data' => $updated]);
}

function handleReviewTx(int $txId, array $adminUser): void
{
    $input = getJsonBody();
    $action = trim($input['action'] ?? '');
    if (!in_array($action, ['approve', 'reject'])) jsonResponse(['success' => false, 'error' => 'validation_error', 'message' => 'Action must be approve or reject'], 422);

    $db = DB::getInstance();
    $tx = $db->fetch("SELECT * FROM transactions WHERE id = ?", [$txId]);
    if (!$tx) jsonResponse(['success' => false, 'error' => 'not_found'], 404);
    if ($tx['status'] !== 'held') jsonResponse(['success' => false, 'error' => 'invalid_state'], 409);

    if ($action === 'approve') {
        $db->execute("UPDATE transactions SET status = 'confirmed', held = 0, reviewed_by = ?, reviewed_at = datetime('now'), updated_at = datetime('now') WHERE id = ?",
            [(int)$adminUser['id'], $txId]);
        logAudit((int)$tx['user_id'], (int)$adminUser['id'], 'tx.released', 'transactions', $txId, ['status' => 'held'], ['status' => 'confirmed']);
    } else {
        $db->execute("UPDATE transactions SET status = 'cancelled', held = 0, reviewed_by = ?, reviewed_at = datetime('now'), updated_at = datetime('now') WHERE id = ?",
            [(int)$adminUser['id'], $txId]);
        logAudit((int)$tx['user_id'], (int)$adminUser['id'], 'tx.cancelled', 'transactions', $txId, ['status' => 'held'], ['status' => 'cancelled']);
    }

    $updated = $db->fetch("SELECT * FROM transactions WHERE id = ?", [$txId]);
    jsonResponse(['success' => true, 'data' => $updated]);
}

function handleTransactionsList(): void
{
    $db = DB::getInstance();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $status = trim($_GET['status'] ?? '');
    $userId = trim($_GET['user_id'] ?? '');
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo = trim($_GET['date_to'] ?? '');

    $where = [];
    $params = [];

    $validStatuses = ['pending', 'confirmed', 'received', 'refunded', 'cancelled', 'held'];
    if (!empty($status) && in_array($status, $validStatuses)) {
        $where[] = "t.status = ?";
        $params[] = $status;
    }
    if (!empty($userId)) {
        $where[] = "t.user_id = ?";
        $params[] = (int)$userId;
    }
    if (!empty($dateFrom)) {
        $where[] = "t.created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    if (!empty($dateTo)) {
        $where[] = "t.created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = (int)$db->fetch("SELECT COUNT(*) as c FROM transactions t {$whereClause}", $params)['c'];

    $allParams = array_merge($params, [$limit, $offset]);
    $txs = $db->fetchAll("SELECT t.*, u.name as user_name, u.email as user_email FROM transactions t LEFT JOIN users u ON t.user_id = u.id {$whereClause} ORDER BY t.created_at DESC LIMIT ? OFFSET ?", $allParams);

    jsonResponse([
        'success' => true,
        'data' => $txs,
        'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => max(1, (int)ceil($total / $limit))],
    ]);
}

function handleAuditLog(): void
{
    $db = DB::getInstance();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 30)));
    $offset = ($page - 1) * $limit;
    $action = trim($_GET['action'] ?? '');
    $userId = trim($_GET['user_id'] ?? '');

    $where = [];
    $params = [];
    if (!empty($action)) { $where[] = "a.action LIKE ?"; $params[] = "%{$action}%"; }
    if (!empty($userId)) { $where[] = "a.user_id = ?"; $params[] = (int)$userId; }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = (int)$db->fetch("SELECT COUNT(*) as c FROM audit_log a {$whereClause}", $params)['c'];

    $allParams = array_merge($params, [$limit, $offset]);
    $logs = $db->fetchAll("SELECT a.*, u.name as user_name, ad.username as admin_username FROM audit_log a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN admin_users ad ON a.admin_id = ad.id {$whereClause} ORDER BY a.created_at DESC LIMIT ? OFFSET ?", $allParams);

    jsonResponse([
        'success' => true,
        'data' => $logs,
        'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => max(1, (int)ceil($total / $limit))],
    ]);
}

function handleGetConfig(): void
{
    $db = DB::getInstance();
    $rows = $db->fetchAll("SELECT * FROM settings ORDER BY key ASC");
    $config = [];
    foreach ($rows as $r) { $config[$r['key']] = $r['value']; }
    jsonResponse(['success' => true, 'data' => $config]);
}

function handleUpdateConfig(array $adminUser): void
{
    $input = getJsonBody();
    $key = trim($input['key'] ?? '');
    $value = isset($input['value']) ? (string)$input['value'] : '';

    if (empty($key)) jsonResponse(['success' => false, 'error' => 'validation_error', 'message' => 'Key required'], 422);

    $db = DB::getInstance();
    $existing = $db->fetch("SELECT * FROM settings WHERE key = ?", [$key]);

    if ($existing) {
        $db->execute("UPDATE settings SET value = ?, updated_at = datetime('now') WHERE key = ?", [$value, $key]);
        $oldValue = $existing['value'];
    } else {
        $db->execute("INSERT INTO settings (key, value) VALUES (?, ?)", [$key, $value]);
        $oldValue = null;
    }

    logAudit(null, (int)$adminUser['id'], 'settings.changed', 'settings', $existing['id'] ?? null,
        ['key' => $key, 'value' => $oldValue],
        ['key' => $key, 'value' => $value]
    );

    jsonResponse(['success' => true, 'data' => ['key' => $key, 'value' => $value]]);
}

handleAdminApi();
