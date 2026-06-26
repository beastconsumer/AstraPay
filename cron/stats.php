<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__) . '/backend';
require_once $baseDir . '/config.php';
require_once $baseDir . '/db.php';

$lockFile = sys_get_temp_dir() . '/astrapay_stats_cron.lock';

if (file_exists($lockFile)) {
    $lockTime = (int) file_get_contents($lockFile);
    if (time() - $lockTime < 300) {
        exit(0);
    }
}

file_put_contents($lockFile, (string) time(), LOCK_EX);

$logDir = LOG_DIR;
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

function statsLog($level, $msg, $data = null)
{
    $ts = date('Y-m-d H:i:s');
    $entry = "[{$ts}] [{$level}] [CronStats] {$msg}";
    if ($data !== null) {
        $entry .= ' | ' . json_encode($data, JSON_UNESCAPED_SLASHES);
    }
    $entry .= PHP_EOL;
    @file_put_contents(LOG_DIR . '/cron.log', $entry, FILE_APPEND | LOCK_EX);
}

try {
    statsLog('INFO', 'started');

    $db = DB::getInstance();

    $totalUsers = (int) ($db->fetch('SELECT COUNT(*) as count FROM users')['count'] ?? 0);
    $activeUsers30d = (int) ($db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM transactions WHERE created_at >= datetime('now', '-30 days')")['count'] ?? 0);
    $newUsersToday = (int) ($db->fetch("SELECT COUNT(*) as count FROM users WHERE date(created_at) = date('now')")['count'] ?? 0);
    $newUsersWeek = (int) ($db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at >= datetime('now', '-7 days')")['count'] ?? 0);
    $newUsersMonth = (int) ($db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at >= datetime('now', '-30 days')")['count'] ?? 0);

    $totalTransactions = (int) ($db->fetch('SELECT COUNT(*) as count FROM transactions')['count'] ?? 0);
    $txToday = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE date(created_at) = date('now')")['count'] ?? 0);
    $txTodayPaid = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE date(updated_at) = date('now') AND status IN ('confirmed', 'received', 'withdrawn')")['count'] ?? 0);
    $txTodayPending = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE date(created_at) = date('now') AND status = 'pending'")['count'] ?? 0);

    $volumeToday = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE date(created_at) = date('now') AND status NOT IN ('cancelled', 'refunded')")['total'] ?? 0);
    $volumeMonth = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now') AND status NOT IN ('cancelled', 'refunded')")['total'] ?? 0);
    $volumeAll = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE status IN ('confirmed', 'received', 'withdrawn')")['total'] ?? 0);

    $feesToday = (float) ($db->fetch("SELECT COALESCE(SUM(fee_amount), 0) as total FROM transactions WHERE date(created_at) = date('now') AND status IN ('confirmed', 'received', 'withdrawn')")['total'] ?? 0);
    $feesMonth = (float) ($db->fetch("SELECT COALESCE(SUM(fee_amount), 0) as total FROM transactions WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now') AND status IN ('confirmed', 'received', 'withdrawn')")['total'] ?? 0);
    $feesAll = (float) ($db->fetch("SELECT COALESCE(SUM(fee_amount), 0) as total FROM transactions WHERE status IN ('confirmed', 'received', 'withdrawn')")['total'] ?? 0);

    $pendingWithdrawals = (int) ($db->fetch("SELECT COUNT(*) as count FROM withdrawals WHERE status IN ('pending', 'processing')")['count'] ?? 0);
    $failedWithdrawals = (int) ($db->fetch("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'failed' AND retry_count >= 3")['count'] ?? 0);
    $heldTransactions = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE held = 1 AND status = 'held'")['count'] ?? 0);
    $bannedUsers = (int) ($db->fetch('SELECT COUNT(*) as count FROM users WHERE banned = 1')['count'] ?? 0);

    $usersByTier = [];
    $tierRows = $db->fetchAll('SELECT tier, COUNT(*) as count FROM users GROUP BY tier');
    foreach ($tierRows as $row) {
        $usersByTier[$row['tier']] = (int) $row['count'];
    }

    $verifiedEmails = (int) ($db->fetch('SELECT COUNT(*) as count FROM users WHERE email_verified = 1')['count'] ?? 0);
    $hasPixKey = (int) ($db->fetch("SELECT COUNT(*) as count FROM users WHERE pix_key IS NOT NULL AND pix_key != ''")['count'] ?? 0);

    $avgTxAmount = $totalTransactions > 0 ? round($volumeAll / $totalTransactions, 2) : 0;

    $statsJson = json_encode([
        'generated_at' => date('c'),
        'users'        => [
            'total'          => $totalUsers,
            'new_today'      => $newUsersToday,
            'new_week'       => $newUsersWeek,
            'new_month'      => $newUsersMonth,
            'active_30d'     => $activeUsers30d,
            'verified_email' => $verifiedEmails,
            'has_pix_key'    => $hasPixKey,
            'banned'         => $bannedUsers,
            'by_tier'        => $usersByTier,
        ],
        'transactions' => [
            'total'        => $totalTransactions,
            'today'        => $txToday,
            'today_paid'   => $txTodayPaid,
            'today_pending' => $txTodayPending,
            'held'         => $heldTransactions,
            'avg_amount'   => $avgTxAmount,
        ],
        'volume'       => [
            'today'    => round($volumeToday, 2),
            'month'    => round($volumeMonth, 2),
            'all_time' => round($volumeAll, 2),
        ],
        'fees'         => [
            'today'    => round($feesToday, 2),
            'month'    => round($feesMonth, 2),
            'all_time' => round($feesAll, 2),
        ],
        'withdrawals'  => [
            'pending' => $pendingWithdrawals,
            'failed'  => $failedWithdrawals,
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $statsPath = DATA_DIR . '/daily_stats.json';
    file_put_contents($statsPath, $statsJson, LOCK_EX);

    statsLog('INFO', 'completed', [
        'users'        => $totalUsers,
        'active_30d'   => $activeUsers30d,
        'tx_today'     => $txToday,
        'volume_today' => round($volumeToday, 2),
        'fees_month'   => round($feesMonth, 2),
        'held'         => $heldTransactions,
        'pending_wd'   => $pendingWithdrawals,
    ]);

    echo sprintf(
        "[%s] Daily stats aggregated.\n" .
        "  Users: %d total | %d new today | %d active (30d) | %d banned\n" .
        "  Transactions: %d total | %d today | %d paid today | %d held\n" .
        "  Volume: R$ %s today | R$ %s month | R$ %s all-time\n" .
        "  Fees: R$ %s today | R$ %s month | R$ %s all-time\n" .
        "  Withdrawals: %d pending | %d permanently failed\n" .
        "  Tiers: %s\n",
        date('Y-m-d H:i:s'),
        $totalUsers, $newUsersToday, $activeUsers30d, $bannedUsers,
        $totalTransactions, $txToday, $txTodayPaid, $heldTransactions,
        number_format($volumeToday, 2, ',', '.'),
        number_format($volumeMonth, 2, ',', '.'),
        number_format($volumeAll, 2, ',', '.'),
        number_format($feesToday, 2, ',', '.'),
        number_format($feesMonth, 2, ',', '.'),
        number_format($feesAll, 2, ',', '.'),
        $pendingWithdrawals, $failedWithdrawals,
        json_encode($usersByTier)
    );

} catch (Exception $e) {
    statsLog('ERROR', 'fatal error', ['error' => $e->getMessage()]);
    echo date('Y-m-d H:i:s') . ' FATAL: ' . $e->getMessage() . "\n";
    exit(1);
} finally {
    @unlink($lockFile);
}
