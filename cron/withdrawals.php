<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__) . '/backend';
require_once $baseDir . '/config.php';
require_once $baseDir . '/db.php';
require_once $baseDir . '/asaas.php';
require_once $baseDir . '/withdraw_processor.php';

$lockFile = sys_get_temp_dir() . '/astrapay_withdrawal_cron.lock';

if (file_exists($lockFile)) {
    $lockTime = (int) file_get_contents($lockFile);
    if (time() - $lockTime < 240) {
        echo date('Y-m-d H:i:s') . " Lock exists, skipping\n";
        exit(0);
    }
    error_log('[AstraPay Cron] Stale withdrawal lock detected, overriding');
}

file_put_contents($lockFile, (string) time(), LOCK_EX);

$logDir = LOG_DIR;
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

function cronLog($level, $msg, $data = null)
{
    $ts = date('Y-m-d H:i:s');
    $entry = "[{$ts}] [{$level}] [CronWithdraw] {$msg}";
    if ($data !== null) {
        $entry .= ' | ' . json_encode($data, JSON_UNESCAPED_SLASHES);
    }
    $entry .= PHP_EOL;
    @file_put_contents(LOG_DIR . '/cron.log', $entry, FILE_APPEND | LOCK_EX);
}

try {
    cronLog('INFO', 'started');

    $db = DB::getInstance();

    $balanceInfo = null;
    try {
        $asaas = new AsaasService($db);
        $balanceInfo = $asaas->getBalance();
        cronLog('INFO', 'Asaas balance checked', $balanceInfo);
    } catch (Exception $e) {
        cronLog('ERROR', 'cannot check Asaas balance', ['error' => $e->getMessage()]);
    }

    $confirmedTx = $db->fetchAll(
        "SELECT t.*, u.pix_key, u.pix_key_type, u.banned
         FROM transactions t JOIN users u ON u.id = t.user_id
         WHERE t.status IN ('confirmed', 'received') AND t.held = 0
         AND NOT EXISTS (SELECT 1 FROM withdrawals w WHERE w.transaction_id = t.id)
         ORDER BY t.created_at ASC LIMIT 50"
    );

    $processed = 0;
    $skipped = 0;
    $failed = 0;

    $processor = new WithdrawalProcessor($db);

    foreach ($confirmedTx as $tx) {
        $txId = $tx['id'];

        if ($tx['banned']) {
            cronLog('INFO', 'skipping banned user', ['tx_id' => $txId, 'user_id' => $tx['user_id']]);
            $skipped++;
            continue;
        }

        if (empty($tx['pix_key'])) {
            $db->execute("UPDATE transactions SET error_message = 'Usuario sem chave PIX configurada', updated_at = datetime('now') WHERE id = ?", [$txId]);
            cronLog('WARNING', 'skipping no PIX key', ['tx_id' => $txId]);
            $skipped++;
            continue;
        }

        $result = $processor->processTransaction($txId);

        if ($result['success']) {
            $processed++;
        } else {
            $failed++;
            cronLog('ERROR', 'tx failed', ['tx_id' => $txId, 'error' => $result['error'] ?? 'unknown']);
        }

        usleep(200000);
    }

    $retryResults = $processor->retryFailedWithdrawals();
    $retried = count($retryResults);
    $retrySuccess = count(array_filter($retryResults, fn($r) => $r['success'] ?? false));

    cronLog('INFO', 'completed', [
        'processed'     => $processed,
        'skipped'       => $skipped,
        'failed'        => $failed,
        'retried'       => $retried,
        'retry_success' => $retrySuccess,
    ]);

    echo sprintf(
        "[%s] Withdrawals: processed=%d skipped=%d failed=%d retried=%d (success=%d)\n",
        date('Y-m-d H:i:s'), $processed, $skipped, $failed, $retried, $retrySuccess
    );

} catch (Exception $e) {
    cronLog('ERROR', 'fatal error', ['error' => $e->getMessage()]);
    echo date('Y-m-d H:i:s') . ' FATAL: ' . $e->getMessage() . "\n";
    exit(1);
} finally {
    @unlink($lockFile);
}
