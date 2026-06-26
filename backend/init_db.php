<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$db = DB::getInstance();

echo "Initializing AstraPay database...\n";

// 2.1 users
$db->execute("
    CREATE TABLE IF NOT EXISTS users (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        email           TEXT    NOT NULL UNIQUE COLLATE NOCASE,
        password_hash   TEXT    NOT NULL,
        name            TEXT    NOT NULL,
        cpf             TEXT    DEFAULT NULL,
        pix_key         TEXT    DEFAULT NULL,
        pix_key_type    TEXT    DEFAULT 'cpf',
        phone           TEXT    DEFAULT NULL,
        email_verified  INTEGER DEFAULT 0,
        tier            TEXT    DEFAULT 'new',
        daily_limit     REAL    DEFAULT 100.00,
        monthly_limit   REAL    DEFAULT 500.00,
        per_tx_limit    REAL    DEFAULT 100.00,
        admin_fee_pct   REAL    DEFAULT 0.00,
        total_received  REAL    DEFAULT 0.00,
        current_balance REAL    DEFAULT 0.00,
        total_withdrawn REAL    DEFAULT 0.00,
        ip_registered   TEXT    DEFAULT NULL,
        ip_last_login   TEXT    DEFAULT NULL,
        user_agent      TEXT    DEFAULT NULL,
        banned          INTEGER DEFAULT 0,
        ban_reason      TEXT    DEFAULT NULL,
        reviewed_by     INTEGER DEFAULT NULL REFERENCES admin_users(id),
        avatar_url      TEXT    DEFAULT NULL,
        last_login_at   TEXT    DEFAULT NULL,
        created_at      TEXT    NOT NULL DEFAULT (datetime('now')),
        updated_at      TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  users table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_users_cpf ON users(cpf)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_users_tier ON users(tier)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_users_banned ON users(banned)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_users_created ON users(created_at)");

// 2.2 transactions
$db->execute("
    CREATE TABLE IF NOT EXISTS transactions (
        id                  INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id             INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        asaas_payment_id    TEXT    DEFAULT NULL,
        external_ref        TEXT    DEFAULT NULL,
        amount              REAL    NOT NULL,
        net_amount          REAL    NOT NULL,
        fee_amount          REAL    NOT NULL,
        fee_percent         REAL    NOT NULL,
        status              TEXT    NOT NULL DEFAULT 'pending',
        payer_name          TEXT    DEFAULT NULL,
        payer_cpf_cnpj      TEXT    DEFAULT NULL,
        payer_email         TEXT    DEFAULT NULL,
        description         TEXT    DEFAULT NULL,
        pix_copy_paste      TEXT    DEFAULT NULL,
        pix_qrcode_url      TEXT    DEFAULT NULL,
        pix_expiration      TEXT    DEFAULT NULL,
        webhook_received_at TEXT    DEFAULT NULL,
        held                INTEGER DEFAULT 0,
        hold_reason         TEXT    DEFAULT NULL,
        reviewed_by         INTEGER DEFAULT NULL REFERENCES admin_users(id),
        reviewed_at         TEXT    DEFAULT NULL,
        error_message       TEXT    DEFAULT NULL,
        created_at          TEXT    NOT NULL DEFAULT (datetime('now')),
        updated_at          TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  transactions table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_tx_user_id ON transactions(user_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_tx_asaas_id ON transactions(asaas_payment_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_tx_status ON transactions(status)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_tx_external_ref ON transactions(external_ref)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_tx_held ON transactions(held)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_tx_created ON transactions(created_at)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_tx_user_status ON transactions(user_id, status)");

// 2.3 withdrawals
$db->execute("
    CREATE TABLE IF NOT EXISTS withdrawals (
        id                  INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id             INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        transaction_id      INTEGER DEFAULT NULL REFERENCES transactions(id),
        amount              REAL    NOT NULL,
        net_amount          REAL    NOT NULL,
        fee_amount          REAL    NOT NULL DEFAULT 0.00,
        pix_key             TEXT    NOT NULL,
        pix_key_type        TEXT    NOT NULL,
        asaas_transfer_id   TEXT    DEFAULT NULL,
        status              TEXT    NOT NULL DEFAULT 'pending',
        error_message       TEXT    DEFAULT NULL,
        retry_count         INTEGER DEFAULT 0,
        next_retry_at       TEXT    DEFAULT NULL,
        completed_at        TEXT    DEFAULT NULL,
        created_at          TEXT    NOT NULL DEFAULT (datetime('now')),
        updated_at          TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  withdrawals table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_wd_user_id ON withdrawals(user_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_wd_status ON withdrawals(status)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_wd_asaas_id ON withdrawals(asaas_transfer_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_wd_created ON withdrawals(created_at)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_wd_retry ON withdrawals(status, next_retry_at)");

// 2.4 settings
$db->execute("
    CREATE TABLE IF NOT EXISTS settings (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        key         TEXT    NOT NULL UNIQUE,
        value       TEXT    NOT NULL,
        description TEXT    DEFAULT NULL,
        updated_at  TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  settings table OK\n";

$existingSettings = $db->fetchAll("SELECT key FROM settings");
$existingKeys = array_column($existingSettings, 'key');

$defaultSettings = [
    ['platform_name', 'AstraPay', 'Nome da plataforma'],
    ['admin_email', '', 'Email do administrador'],
    ['default_admin_fee_pct', '0', 'Taxa de admin padrão (%)'],
    ['min_withdrawal_amount', '50', 'Valor mínimo para saque (R$)'],
    ['max_withdrawal_amount', '10000', 'Valor máximo por saque (R$)'],
    ['withdrawal_cooldown_minutes', '60', 'Intervalo mínimo entre saques'],
    ['asaas_api_key', ASAAS_API_KEY, 'Chave API Asaas (production)'],
    ['asaas_api_url', 'https://api.asaas.com/v3', 'URL base da API Asaas'],
    ['asaas_webhook_secret', '', 'Token secreto do webhook'],
    ['email_from', 'noreply@astrapay.com.br', 'Email remetente'],
    ['email_smtp_host', '', 'SMTP host'],
    ['email_smtp_port', '587', 'SMTP port'],
    ['email_smtp_user', '', 'SMTP user'],
    ['email_smtp_pass', '', 'SMTP password'],
    ['fraud_max_accounts_per_ip', '3', 'Max accounts per IP'],
    ['fraud_auto_hold_amount', '5000', 'Auto-hold PIX above this amount (R$)'],
    ['maintenance_mode', '0', '0=normal 1=maintenance'],
];

foreach ($defaultSettings as $setting) {
    if (!in_array($setting[0], $existingKeys)) {
        $db->execute(
            "INSERT INTO settings (key, value, description) VALUES (?, ?, ?)",
            [$setting[0], $setting[1], $setting[2]]
        );
    }
}
echo "  settings defaults OK\n";

// 2.5 audit_log
$db->execute("
    CREATE TABLE IF NOT EXISTS audit_log (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER DEFAULT NULL,
        admin_id    INTEGER DEFAULT NULL REFERENCES admin_users(id),
        action      TEXT    NOT NULL,
        entity_type TEXT    DEFAULT NULL,
        entity_id   INTEGER DEFAULT NULL,
        old_values  TEXT    DEFAULT NULL,
        new_values  TEXT    DEFAULT NULL,
        ip_address  TEXT    DEFAULT NULL,
        user_agent  TEXT    DEFAULT NULL,
        metadata    TEXT    DEFAULT NULL,
        created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  audit_log table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_audit_user_id ON audit_log(user_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_audit_admin_id ON audit_log(admin_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_audit_action ON audit_log(action)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_audit_entity ON audit_log(entity_type, entity_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_log(created_at)");

// 2.6 email_verifications
$db->execute("
    CREATE TABLE IF NOT EXISTS email_verifications (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        token       TEXT    NOT NULL UNIQUE,
        type        TEXT    NOT NULL DEFAULT 'email_verify',
        expires_at  TEXT    NOT NULL,
        used        INTEGER NOT NULL DEFAULT 0,
        created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  email_verifications table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_ev_token ON email_verifications(token)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_ev_user_type ON email_verifications(user_id, type, used)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_ev_expires ON email_verifications(expires_at)");

// 2.7 admin_users
$db->execute("
    CREATE TABLE IF NOT EXISTS admin_users (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        username        TEXT    NOT NULL UNIQUE,
        password_hash   TEXT    NOT NULL,
        role            TEXT    NOT NULL DEFAULT 'viewer',
        ip_whitelist    TEXT    DEFAULT NULL,
        two_factor_enabled INTEGER DEFAULT 0,
        is_active       INTEGER NOT NULL DEFAULT 1,
        last_login_at   TEXT    DEFAULT NULL,
        last_login_ip   TEXT    DEFAULT NULL,
        created_at      TEXT    NOT NULL DEFAULT (datetime('now')),
        updated_at      TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  admin_users table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_admin_username ON admin_users(username)");

// 2.8 login_attempts
$db->execute("
    CREATE TABLE IF NOT EXISTS login_attempts (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_address  TEXT    NOT NULL,
        email       TEXT    DEFAULT NULL,
        type        TEXT    NOT NULL DEFAULT 'login',
        success     INTEGER NOT NULL DEFAULT 0,
        created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  login_attempts table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_la_ip ON login_attempts(ip_address, created_at)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_la_email ON login_attempts(email, created_at)");

// session_tokens — Bearer token auth
$db->execute("
    CREATE TABLE IF NOT EXISTS session_tokens (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id       INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE CASCADE,
        admin_user_id INTEGER DEFAULT NULL REFERENCES admin_users(id) ON DELETE CASCADE,
        token         TEXT    NOT NULL UNIQUE,
        expires_at    TEXT    NOT NULL,
        created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
    )
");
echo "  session_tokens table OK\n";

$db->execute("CREATE INDEX IF NOT EXISTS idx_st_token ON session_tokens(token)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_st_user ON session_tokens(user_id)");
$db->execute("CREATE INDEX IF NOT EXISTS idx_st_admin ON session_tokens(admin_user_id)");

echo "\nDatabase initialized successfully at: " . DB_PATH . "\n";
echo "WAL mode: ON | Foreign keys: ON\n";
