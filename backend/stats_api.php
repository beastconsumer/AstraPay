<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';

function handle_user_stats($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $currentBalance = (float) ($db->fetch('SELECT current_balance FROM users WHERE id = ?', [$userId])['current_balance'] ?? 0);
    $totalReceived = (float) ($db->fetch('SELECT total_received FROM users WHERE id = ?', [$userId])['total_received'] ?? 0);
    $totalWithdrawn = (float) ($db->fetch('SELECT total_withdrawn FROM users WHERE id = ?', [$userId])['total_withdrawn'] ?? 0);

    $pixGenerated = (int) ($db->fetch('SELECT COUNT(*) as count FROM transactions WHERE user_id = ?', [$userId])['count'] ?? 0);
    $pixPaid = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status IN ('confirmed', 'received', 'withdrawn')", [$userId])['count'] ?? 0);
    $pendingCount = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status = 'pending'", [$userId])['count'] ?? 0);

    $volumeToday = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND date(created_at) = date('now') AND status NOT IN ('cancelled', 'refunded')", [$userId])['total'] ?? 0);
    $volumeMonth = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now') AND status NOT IN ('cancelled', 'refunded')", [$userId])['total'] ?? 0);

    $confirmedToday = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status IN ('confirmed', 'received', 'withdrawn') AND date(updated_at) = date('now')", [$userId])['count'] ?? 0);

    $feePercent = (float) ($user['admin_fee_pct'] ?? 0);
    $tier = $user['tier'] ?: 'new';

    return [
        'body' => ['success' => true, 'data' => [
            'saldo'            => round($currentBalance, 2),
            'total_recebido'   => round($totalReceived, 2),
            'total_withdrawn'  => round($totalWithdrawn, 2),
            'pix_gerados'      => $pixGenerated,
            'pix_pagos'        => $pixPaid,
            'pix_pendentes'    => $pendingCount,
            'fee_pct'          => $feePercent,
            'tier'             => $tier,
            'volume_hoje'      => round($volumeToday, 2),
            'volume_mes'       => round($volumeMonth, 2),
            'confirmados_hoje' => $confirmedToday,
            'limits'           => [
                'daily'   => (float) ($user['daily_limit'] ?? 100),
                'monthly' => (float) ($user['monthly_limit'] ?? 500),
                'per_tx'  => (float) ($user['per_tx_limit'] ?? 100),
            ],
        ]],
        'status' => 200,
    ];
}

function handle_user_dashboard($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $currentBalance = (float) ($db->fetch('SELECT current_balance FROM users WHERE id = ?', [$userId])['current_balance'] ?? 0);
    $totalReceived = (float) ($db->fetch('SELECT total_received FROM users WHERE id = ?', [$userId])['total_received'] ?? 0);
    $totalWithdrawn = (float) ($db->fetch('SELECT total_withdrawn FROM users WHERE id = ?', [$userId])['total_withdrawn'] ?? 0);

    $totalReceivedMonth = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now') AND status IN ('confirmed', 'received', 'withdrawn')", [$userId])['total'] ?? 0);

    $pendingCount = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status = 'pending'", [$userId])['count'] ?? 0);
    $confirmedToday = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status IN ('confirmed', 'received', 'withdrawn') AND date(updated_at) = date('now')", [$userId])['count'] ?? 0);

    $recentTransactions = $db->fetchAll(
        "SELECT id, asaas_payment_id, amount, net_amount, fee_amount, status, description, payer_name, created_at
         FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
        [$userId]
    );

    $dailyVolume = $db->fetchAll(
        "SELECT date(created_at) as date, COALESCE(SUM(amount), 0) as amount, COUNT(*) as count
         FROM transactions WHERE user_id = ? AND created_at >= datetime('now', '-30 days') AND status NOT IN ('cancelled', 'refunded')
         GROUP BY date(created_at) ORDER BY date ASC",
        [$userId]
    );

    $totalTransactions = (int) ($db->fetch('SELECT COUNT(*) as count FROM transactions WHERE user_id = ?', [$userId])['count'] ?? 0);

    $tier = $user['tier'] ?: 'new';
    $emailVerified = (bool) ($user['email_verified'] ?? false);

    $tierProgress = null;
    $tierOrder = ['new' => 0, 'basic' => 1, 'bronze' => 2, 'silver' => 3, 'gold' => 4];
    $currentTierLevel = $tierOrder[$tier] ?? 0;

    if ($currentTierLevel < 4) {
        $nextTiers = ['new' => 'basic', 'basic' => 'bronze', 'bronze' => 'silver', 'silver' => 'gold'];
        $nextTier = $nextTiers[$tier] ?? null;

        if ($nextTier) {
            $requirements = get_tier_requirements($nextTier);
            $daysOnPlatform = null;

            if ($user['created_at']) {
                $created = new DateTime($user['created_at']);
                $now = new DateTime();
                $daysOnPlatform = (int) $created->diff($now)->days;
            }

            $txsNeeded = $requirements['transactions'] > $totalTransactions ? $requirements['transactions'] - $totalTransactions : 0;
            $daysNeeded = $requirements['days'] > $daysOnPlatform ? $requirements['days'] - $daysOnPlatform : 0;

            $tierProgress = [
                'current_level'       => $tier,
                'next_level'          => $nextTier,
                'transactions_needed' => $txsNeeded,
                'days_needed'         => $daysNeeded,
                'email_verified'      => $emailVerified,
                'cpf_validated'       => !empty($user['cpf']),
            ];
        }
    }

    return [
        'body' => ['success' => true, 'data' => [
            'current_balance'      => round($currentBalance, 2),
            'total_received_all'   => round($totalReceived, 2),
            'total_received_month' => round($totalReceivedMonth, 2),
            'total_withdrawn'      => round($totalWithdrawn, 2),
            'total_transactions'   => $totalTransactions,
            'pending_count'        => $pendingCount,
            'confirmed_today'      => $confirmedToday,
            'tier'                 => $tier,
            'tier_progress'        => $tierProgress,
            'email_verified'       => $emailVerified,
            'fee_percent'          => (float) ($user['admin_fee_pct'] ?? 0),
            'recent_transactions'  => $recentTransactions,
            'daily_volume'         => $dailyVolume,
            'limits'               => [
                'daily'   => (float) ($user['daily_limit'] ?? 100),
                'monthly' => (float) ($user['monthly_limit'] ?? 500),
                'per_tx'  => (float) ($user['per_tx_limit'] ?? 100),
            ],
        ]],
        'status' => 200,
    ];
}

function get_tier_requirements($tier)
{
    $map = [
        'basic'  => ['transactions' => 0,  'days' => 0],
        'bronze' => ['transactions' => 50,  'days' => 30],
        'silver' => ['transactions' => 200, 'days' => 90],
        'gold'   => ['transactions' => 500, 'days' => 180],
    ];
    return $map[$tier] ?? ['transactions' => 0, 'days' => 0];
}
