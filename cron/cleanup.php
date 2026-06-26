<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__) . '/backend';
require_once $baseDir . '/config.php';
require_once $baseDir . '/db.php';

$lockFile = sys_get_temp_dir() . '/astrapay_cleanup_cron.lock';

if (file_exists($lockFile)) {
    $lockTime = (int) file_get_contents($lockFile);
    if (time() - $lockTime < 3000) {
        exit(0);
    }
}

file_put_contents($lockFile, (string) time(), LOCK_EX);

$logDir = LOG_DIR;
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

function cleanupLog($level, $msg, $data = null)
{
    $ts = date('Y-m-d H:i:s');
    $entry = "[{$ts}] [{$level}] [CronCleanup] {$msg}";
    if ($data !== null) {
        $entry .= ' | ' . json_encode($data, JSON_UNESCAPED_SLASHES);
    }
    $entry .= PHP_EOL;
    @file_put_contents(LOG_DIR . '/cron.log', $entry, FILE_APPEND | LOCK_EX);
}

try {
    cleanupLog('INFO', 'started');

    $db = DB::getInstance();

    $deletedVerifications = $db->execute(
        "DELETE FROM email_verifications WHERE expires_at < datetime('now') AND used = 0"
    );

    $deletedLoginAttempts = $db->execute(
        "DELETE FROM login_attempts WHERE created_at < datetime('now', '-7 days')"
    );

    $cleanedSessionTokens = $db->execute(
        "DELETE FROM session_tokens WHERE expires_at < datetime('now')"
    );

    $cleanedTransactions = $db->execute(
        "UPDATE transactions SET pix_copy_paste = NULL, pix_qrcode_url = NULL
         WHERE status IN ('cancelled', 'refunded') AND pix_expiration < datetime('now', '-30 days')
         AND (pix_copy_paste IS NOT NULL OR pix_qrcode_url IS NOT NULL)"
    );

    $db->getPdo()->exec('PRAGMA wal_checkpoint(TRUNCATE)');

    $dbSize = @filesize(DB_PATH);
    $dbSizeMb = $dbSize !== false ? round($dbSize / 1024 / 1024, 2) : 'unknown';

    $db->getPdo()->exec('PRAGMA optimize');

    $countUsers = (int) ($db->fetch('SELECT COUNT(*) as count FROM users')['count'] ?? 0);
    $countTx = (int) ($db->fetch('SELECT COUNT(*) as count FROM transactions')['count'] ?? 0);
    $countWd = (int) ($db->fetch('SELECT COUNT(*) as count FROM withdrawals')['count'] ?? 0);
    $countTokens = (int) ($db->fetch('SELECT COUNT(*) as count FROM session_tokens')['count'] ?? 0);

    cleanupLog('INFO', 'completed', [
        'deleted_verifications' => $deletedVerifications,
        'deleted_login_attempts' => $deletedLoginAttempts,
        'cleaned_session_tokens' => $cleanedSessionTokens,
        'cleaned_transactions'   => $cleanedTransactions,
        'db_size_mb'             => $dbSizeMb,
        'users'                  => $countUsers,
        'transactions'           => $countTx,
        'withdrawals'            => $countWd,
        'active_tokens'          => $countTokens,
    ]);

    echo sprintf(
        "[%s] Cleanup: verifications=%d logins=%d tokens=%d pix_cleaned=%d db=%sMB users=%d tx=%d\n",
        date('Y-m-d H:i:s'), $deletedVerifications, $deletedLoginAttempts, $cleanedSessionTokens,
        $cleanedTransactions, $dbSizeMb, $countUsers, $countTx
    );

} catch (Exception $e) {
    cleanupLog('ERROR', 'fatal error', ['error' => $e->getMessage()]);
    echo date('Y-m-d H:i:s') . ' FATAL: ' . $e->getMessage() . "\n";
    exit(1);
} finally {
    @unlink($lockFile);
}
