<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

function handleRegister(array $input): array
{
    $ip = getClientIP();

    if (!rateLimit('register:' . $ip, 3, 3600)) {
        return [
            'status' => 429,
            'body' => [
                'success' => false,
                'error' => 'rate_limit',
                'message' => 'Muitas tentativas de registro. Aguarde 1 hora.',
                'retry_after' => 3600
            ]
        ];
    }

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $cpf = trim($input['cpf'] ?? '');
    $phone = trim($input['phone'] ?? '');

    $errors = [];

    if (strlen($name) < 2) {
        $errors[] = 'Nome deve ter pelo menos 2 caracteres';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Senha deve ter pelo menos 8 caracteres';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Senha deve conter pelo menos 1 letra maiúscula';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Senha deve conter pelo menos 1 número';
    }

    if (strlen($password) > 72) {
        $errors[] = 'Senha não pode ter mais de 72 caracteres';
    }

    $cpfResult = null;
    if (!empty($cpf)) {
        $cpfResult = validateCPF($cpf);
        if (!$cpfResult['valid']) {
            $errors[] = 'CPF: ' . $cpfResult['error'];
        }
    }

    if (!empty($errors)) {
        recordLoginAttempt($ip, $email, 'register', false);
        return [
            'status' => 422,
            'body' => [
                'success' => false,
                'error' => 'validation_error',
                'message' => 'Dados inválidos',
                'errors' => $errors
            ]
        ];
    }

    $db = DB::getInstance();

    $existingEmail = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingEmail) {
        recordLoginAttempt($ip, $email, 'register', false);
        return [
            'status' => 409,
            'body' => [
                'success' => false,
                'error' => 'email_exists',
                'message' => 'Este email já está cadastrado'
            ]
        ];
    }

    if ($cpfResult && $cpfResult['valid']) {
        $existingCpf = $db->fetch("SELECT id FROM users WHERE cpf = ?", [$cpfResult['clean']]);
        if ($existingCpf) {
            recordLoginAttempt($ip, $email, 'register', false);
            return [
                'status' => 409,
                'body' => [
                    'success' => false,
                    'error' => 'cpf_exists',
                    'message' => 'Este CPF já está cadastrado'
                ]
            ];
        }
    }

    $sameIpAccounts = $db->fetch("SELECT COUNT(*) as cnt FROM users WHERE ip_registered = ?", [$ip]);
    $maxAccounts = intval($db->fetch("SELECT value FROM settings WHERE key = 'fraud_max_accounts_per_ip'")['value'] ?? 3);
    if ($sameIpAccounts['cnt'] >= $maxAccounts) {
        recordLoginAttempt($ip, $email, 'register', false);
        return [
            'status' => 403,
            'body' => [
                'success' => false,
                'error' => 'ip_limit',
                'message' => 'Número máximo de contas para este IP atingido'
            ]
        ];
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $db->execute(
        "INSERT INTO users (email, password_hash, name, cpf, phone, ip_registered, user_agent, tier, email_verified)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'new', 0)",
        [
            $email,
            $passwordHash,
            $name,
            $cpfResult ? $cpfResult['clean'] : null,
            !empty($phone) ? $phone : null,
            $ip,
            getClientUA()
        ]
    );

    $userId = $db->lastInsertId();
    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY_SECONDS);

    $db->execute(
        "INSERT INTO session_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
        [$userId, $token, $expiresAt]
    );

    $verifyToken = generateToken();
    $verifyExpires = date('Y-m-d H:i:s', time() + (EMAIL_VERIFY_EXPIRY_HOURS * 3600));
    $db->execute(
        "INSERT INTO email_verifications (user_id, token, type, expires_at) VALUES (?, ?, 'email_verify', ?)",
        [$userId, $verifyToken, $verifyExpires]
    );

    logAudit($userId, null, 'user.registered', 'users', (int)$userId, null, [
        'email' => $email,
        'name' => $name,
        'tier' => 'new',
        'ip' => $ip
    ]);

    recordLoginAttempt($ip, $email, 'register', true);

    $user = $db->fetch("SELECT id, email, name, cpf, phone, email_verified, tier, daily_limit, monthly_limit, per_tx_limit, admin_fee_pct, current_balance, total_received, total_withdrawn, created_at FROM users WHERE id = ?", [$userId]);

    return [
        'status' => 201,
        'body' => [
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user,
                'message' => 'Conta criada com sucesso. Verifique seu email.',
                'verification_token_log' => $verifyToken
            ]
        ]
    ];
}

function handleLogin(array $input): array
{
    $ip = getClientIP();
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (!rateLimit('login_ip:' . $ip, 5, 60)) {
        return [
            'status' => 429,
            'body' => [
                'success' => false,
                'error' => 'rate_limit',
                'message' => 'Muitas tentativas de login. Aguarde 60 segundos.',
                'retry_after' => 60
            ]
        ];
    }

    if (!empty($email) && !rateLimit('login_email:' . strtolower($email), 10, 60)) {
        return [
            'status' => 429,
            'body' => [
                'success' => false,
                'error' => 'rate_limit',
                'message' => 'Muitas tentativas para este email. Aguarde 60 segundos.',
                'retry_after' => 60
            ]
        ];
    }

    if (empty($email) || empty($password)) {
        recordLoginAttempt($ip, $email, 'login', false);
        return [
            'status' => 422,
            'body' => [
                'success' => false,
                'error' => 'validation_error',
                'message' => 'Email e senha são obrigatórios'
            ]
        ];
    }

    $db = DB::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        recordLoginAttempt($ip, $email, 'login', false);
        return [
            'status' => 401,
            'body' => [
                'success' => false,
                'error' => 'invalid_credentials',
                'message' => 'Email ou senha inválidos'
            ]
        ];
    }

    if ($user['banned']) {
        recordLoginAttempt($ip, $email, 'login', false);
        return [
            'status' => 423,
            'body' => [
                'success' => false,
                'error' => 'banned',
                'message' => 'Conta suspensa: ' . ($user['ban_reason'] ?: 'Violação dos termos de uso')
            ]
        ];
    }

    $token = generateToken();
    $expiresAt = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY_SECONDS);

    $db->execute(
        "INSERT INTO session_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
        [$user['id'], $token, $expiresAt]
    );

    $db->execute(
        "UPDATE users SET last_login_at = datetime('now'), ip_last_login = ?, updated_at = datetime('now') WHERE id = ?",
        [$ip, $user['id']]
    );

    logAudit((int)$user['id'], null, 'user.login', 'users', (int)$user['id'], null, null, ['ip' => $ip]);
    recordLoginAttempt($ip, $email, 'login', true);

    unset($user['password_hash']);

    return [
        'status' => 200,
        'body' => [
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]
    ];
}

function handleVerifyEmail(array $input): array
{
    $requestToken = trim($input['token'] ?? '');

    if (empty($requestToken)) {
        return [
            'status' => 422,
            'body' => [
                'success' => false,
                'error' => 'validation_error',
                'message' => 'Token é obrigatório'
            ]
        ];
    }

    $db = DB::getInstance();

    $verification = $db->fetch(
        "SELECT * FROM email_verifications WHERE token = ? AND type = 'email_verify' AND used = 0",
        [$requestToken]
    );

    if (!$verification) {
        return [
            'status' => 404,
            'body' => [
                'success' => false,
                'error' => 'not_found',
                'message' => 'Token inválido ou já utilizado'
            ]
        ];
    }

    if (strtotime($verification['expires_at']) < time()) {
        return [
            'status' => 404,
            'body' => [
                'success' => false,
                'error' => 'not_found',
                'message' => 'Token expirado. Solicite um novo email de verificação.'
            ]
        ];
    }

    $db->execute(
        "UPDATE email_verifications SET used = 1 WHERE id = ?",
        [$verification['id']]
    );

    $db->execute(
        "UPDATE users SET email_verified = 1, updated_at = datetime('now') WHERE id = ?",
        [$verification['user_id']]
    );

    $user = $db->fetch("SELECT tier FROM users WHERE id = ?", [$verification['user_id']]);
    $tierUpgraded = null;

    if ($user && $user['tier'] === 'new') {
        $db->execute(
            "UPDATE users SET tier = 'basic', daily_limit = 1000.00, monthly_limit = 5000.00, per_tx_limit = 500.00, updated_at = datetime('now') WHERE id = ?",
            [$verification['user_id']]
        );
        $tierUpgraded = 'basic';
        logAudit((int)$verification['user_id'], null, 'user.tier_changed', 'users', (int)$verification['user_id'],
            ['tier' => 'new'],
            ['tier' => 'basic'],
            ['reason' => 'email_verified']
        );
    }

    logAudit((int)$verification['user_id'], null, 'user.verified_email', 'users', (int)$verification['user_id']);

    return [
        'status' => 200,
        'body' => [
            'success' => true,
            'data' => [
                'message' => 'Email verificado com sucesso',
                'tier_upgraded_to' => $tierUpgraded
            ]
        ]
    ];
}

function handleMe(array $input): array
{
    $user = auth();
    unset($user['password_hash']);

    return [
        'status' => 200,
        'body' => [
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]
    ];
}

function handleSendVerification(array $input): array
{
    $user = auth();

    if ($user['email_verified']) {
        return [
            'status' => 200,
            'body' => [
                'success' => true,
                'data' => [
                    'message' => 'Email já verificado anteriormente'
                ]
            ]
        ];
    }

    $db = DB::getInstance();

    $recentTokens = $db->fetch(
        "SELECT COUNT(*) as cnt FROM email_verifications WHERE user_id = ? AND type = 'email_verify' AND created_at > datetime('now', '-1 hour')",
        [$user['id']]
    );

    if ($recentTokens['cnt'] >= 3) {
        return [
            'status' => 429,
            'body' => [
                'success' => false,
                'error' => 'rate_limit',
                'message' => 'Muitas solicitações. Aguarde 1 hora para solicitar novo email.',
                'retry_after' => 3600
            ]
        ];
    }

    $verifyToken = generateToken();
    $verifyExpires = date('Y-m-d H:i:s', time() + (EMAIL_VERIFY_EXPIRY_HOURS * 3600));

    $db->execute(
        "INSERT INTO email_verifications (user_id, token, type, expires_at) VALUES (?, ?, 'email_verify', ?)",
        [$user['id'], $verifyToken, $verifyExpires]
    );

    $verifyLink = APP_URL . '/api/auth/verify-email?token=' . $verifyToken;
    error_log('[AstraPay] Verification token for ' . $user['email'] . ': ' . $verifyToken);
    error_log('[AstraPay] Verification link: ' . $verifyLink);

    logAudit((int)$user['id'], null, 'user.verify_email_sent', 'email_verifications', null, null, [
        'token_log' => $verifyToken
    ]);

    return [
        'status' => 200,
        'body' => [
            'success' => true,
            'data' => [
                'message' => 'Email de verificação gerado. Verifique o log do servidor para o token.',
                'verification_token_log' => $verifyToken,
                'verification_link' => $verifyLink
            ]
        ]
    ];
}
