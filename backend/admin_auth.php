<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

function adminCreateFirst(string $username, string $password, string $role = 'super_admin'): array
{
    $db = DB::getInstance();

    $count = $db->fetch("SELECT COUNT(*) as cnt FROM admin_users")['cnt'];
    if ($count > 0) {
        return ['success' => false, 'error' => 'admin_exists', 'message' => 'Admin users already exist.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $db->execute(
        "INSERT INTO admin_users (username, password_hash, role, is_active) VALUES (?, ?, ?, 1)",
        [$username, $hash, $role]
    );
    $adminId = $db->lastInsertId();

    logAudit(null, (int)$adminId, 'admin.created', 'admin_users', (int)$adminId, null, ['username' => $username, 'role' => $role]);

    return [
        'success' => true,
        'data' => ['id' => (int)$adminId, 'username' => $username, 'role' => $role]
    ];
}

function adminLogin(string $username, string $password): array
{
    $db = DB::getInstance();
    $ip = getClientIP();

    $admin = $db->fetch("SELECT * FROM admin_users WHERE username = ? AND is_active = 1", [$username]);

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        recordLoginAttempt($ip, $username, 'admin_login', false);
        logAudit(null, null, 'admin.login_failed', 'admin_users', null, null, null, ['username' => $username]);
        return ['success' => false, 'error' => 'invalid_credentials', 'message' => 'Invalid username or password'];
    }

    if (!empty($admin['ip_whitelist'])) {
        $allowed = json_decode($admin['ip_whitelist'], true);
        if (is_array($allowed) && !empty($allowed) && !ipInRange($ip, $allowed)) {
            logAudit(null, (int)$admin['id'], 'admin.login_ip_denied', 'admin_users', (int)$admin['id'], null, null, ['ip' => $ip]);
            return ['success' => false, 'error' => 'ip_not_allowed', 'message' => 'IP not whitelisted for this admin'];
        }
    }

    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + 86400);

    $db->execute(
        "INSERT INTO session_tokens (admin_user_id, token, expires_at) VALUES (?, ?, ?)",
        [$admin['id'], $token, $expiresAt]
    );

    $db->execute(
        "UPDATE admin_users SET last_login_at = datetime('now'), last_login_ip = ? WHERE id = ?",
        [$ip, $admin['id']]
    );

    if (session_status() === PHP_SESSION_NONE) session_start();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];

    recordLoginAttempt($ip, $username, 'admin_login', true);
    logAudit(null, (int)$admin['id'], 'admin.login', 'admin_users', (int)$admin['id']);

    return [
        'success' => true,
        'data' => [
            'admin' => [
                'id' => (int)$admin['id'],
                'username' => $admin['username'],
                'role' => $admin['role'],
            ],
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ]
    ];
}

function adminLogout(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $adminId = $_SESSION['admin_id'] ?? null;

    if ($adminId) {
        $db = DB::getInstance();
        $db->execute("DELETE FROM session_tokens WHERE admin_user_id = ?", [$adminId]);
        logAudit(null, (int)$adminId, 'admin.logout', 'admin_users', (int)$adminId);
    }

    unset($_SESSION['admin_id']);
    session_destroy();
}

function getAdminById(int $id): ?array
{
    return DB::getInstance()->fetch("SELECT * FROM admin_users WHERE id = ? AND is_active = 1", [$id]);
}

function isAdminLoggedIn(): bool
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['admin_id']);
}

function requireAdminPageAuth(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . APP_URL . '/admin/login');
        exit;
    }
}
