<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__) . '/backend';
require_once $baseDir . '/config.php';
require_once $baseDir . '/db.php';
require_once $baseDir . '/asaas.php';

$lockFile = sys_get_temp_dir() . '/astrapay_health_cron.lock';

if (file_exists($lockFile)) {
    $lockTime = (int) file_get_contents($lockFile);
    if (time() - $lockTime < 240) {
        exit(0);
    }
}

file_put_contents($lockFile, (string) time(), LOCK_EX);

$logDir = LOG_DIR;
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

function healthLog($level, $msg, $data = null)
{
    $ts = date('Y-m-d H:i:s');
    $entry = "[{$ts}] [{$level}] [CronHealth] {$msg}";
    if ($data !== null) {
        $entry .= ' | ' . json_encode($data, JSON_UNESCAPED_SLASHES);
    }
    $entry .= PHP_EOL;
    @file_put_contents(LOG_DIR . '/cron.log', $entry, FILE_APPEND | LOCK_EX);
}

$checks = [
    'database'   => false,
    'asaas_api'  => false,
    'disk_space' => false,
    'webhook'    => false,
    'tables'     => false,
];

try {
    $db = DB::getInstance();
    $result = $db->fetch('SELECT 1 as ok');
    $checks['database'] = $result !== false;
    healthLog('INFO', 'database OK');
} catch (Exception $e) {
    healthLog('ERROR', 'database FAIL', ['error' => $e->getMessage()]);
}

try {
    $tableCount = (int) ($db->fetch(
        "SELECT COUNT(*) as count FROM sqlite_master WHERE type = 'table' AND name IN ('users','transactions','withdrawals','settings','audit_log','email_verifications','admin_users','login_attempts','session_tokens')"
    )['count'] ?? 0);
    $checks['tables'] = $tableCount >= 7;
    healthLog('INFO', 'tables OK', ['count' => $tableCount]);
} catch (Exception $e) {
    healthLog('ERROR', 'tables check FAIL', ['error' => $e->getMessage()]);
}

try {
    $asaas = new AsaasService($db);
    $balance = $asaas->getBalance();
    $checks['asaas_api'] = isset($balance['balance']);
    healthLog('INFO', 'Asaas API OK', ['balance' => $balance['balance'] ?? 'N/A']);
} catch (Exception $e) {
    $checks['asaas_api'] = false;
    healthLog('ERROR', 'Asaas API FAIL', ['error' => $e->getMessage()]);
}

try {
    $dbPath = DB_PATH;
    if (file_exists($dbPath)) {
        $freeSpace = disk_free_space(dirname($dbPath));
        $totalSpace = disk_total_space(dirname($dbPath));
        if ($totalSpace > 0) {
            $usedPercent = round(($totalSpace - $freeSpace) / $totalSpace * 100, 1);
            $checks['disk_space'] = $usedPercent < 90;
            $level = $usedPercent >= 90 ? 'WARNING' : 'INFO';
            healthLog($level, 'disk space', ['used_percent' => $usedPercent, 'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2)]);
        } else {
            $checks['disk_space'] = true;
        }
    }
} catch (Exception $e) {
    healthLog('ERROR', 'disk check FAIL', ['error' => $e->getMessage()]);
}

try {
    $lastWebhook = $db->fetch(
        "SELECT created_at FROM transactions WHERE webhook_received_at IS NOT NULL ORDER BY webhook_received_at DESC LIMIT 1"
    );
    if ($lastWebhook) {
        $lastTime = strtotime($lastWebhook['created_at']);
        $checks['webhook'] = (time() - $lastTime) < 86400;
        healthLog('INFO', 'webhook activity', ['last' => $lastWebhook['created_at']]);
    } else {
        $checks['webhook'] = true;
        healthLog('INFO', 'webhook activity - no history yet');
    }
} catch (Exception $e) {
    healthLog('ERROR', 'webhook check FAIL', ['error' => $e->getMessage()]);
}

$allPassed = !in_array(false, $checks, true);
$failedChecks = array_keys(array_filter($checks, fn($v) => !$v));

if (!$allPassed) {
    healthLog('CRITICAL', 'FAILURES DETECTED', ['checks' => $checks, 'failed' => $failedChecks]);
} else {
    healthLog('INFO', 'all systems OK');
}

echo sprintf(
    "[%s] Health: %s | DB=%s Asaas=%s Disk=%s Webhook=%s Tables=%s\n",
    date('Y-m-d H:i:s'),
    $allPassed ? 'PASSED' : 'FAILED',
    $checks['database'] ? 'OK' : 'FAIL',
    $checks['asaas_api'] ? 'OK' : 'FAIL',
    $checks['disk_space'] ? 'OK' : 'FAIL',
    $checks['webhook'] ? 'OK' : 'FAIL',
    $checks['tables'] ? 'OK' : 'FAIL'
);

if (!$allPassed) {
    echo "FAILED: " . implode(', ', $failedChecks) . "\n";
}

@unlink($lockFile);
exit($allPassed ? 0 : 1);
