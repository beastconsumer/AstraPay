<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/asaas.php';

function handle_withdraw_request($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $amount = isset($input['amount']) ? (float) $input['amount'] : 0;

    if ($amount <= 0) {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'Valor deve ser maior que zero'], 'status' => 422];
    }

    $minWithdrawal = (float) ($db->fetch("SELECT value FROM settings WHERE key = 'min_withdrawal_amount'")['value'] ?? 50);
    $maxWithdrawal = (float) ($db->fetch("SELECT value FROM settings WHERE key = 'max_withdrawal_amount'")['value'] ?? 10000);
    $cooldownMinutes = (int) ($db->fetch("SELECT value FROM settings WHERE key = 'withdrawal_cooldown_minutes'")['value'] ?? 60);

    if ($amount < $minWithdrawal) {
        return ['body' => ['success' => false, 'error' => 'below_min_withdrawal', 'message' => 'Valor minimo para saque: R$ ' . number_format($minWithdrawal, 2, ',', '.')], 'status' => 422];
    }
    if ($amount > $maxWithdrawal) {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'Valor maximo por saque: R$ ' . number_format($maxWithdrawal, 2, ',', '.')], 'status' => 422];
    }

    $currentBalance = (float) ($db->fetch('SELECT current_balance FROM users WHERE id = ?', [$userId])['current_balance'] ?? 0);
    if ($amount > $currentBalance) {
        return ['body' => ['success' => false, 'error' => 'insufficient_balance', 'message' => 'Saldo insuficiente. Disponivel: R$ ' . number_format($currentBalance, 2, ',', '.')], 'status' => 422];
    }

    if (empty($user['pix_key'])) {
        return ['body' => ['success' => false, 'error' => 'invalid_pix_key', 'message' => 'Configure sua chave PIX antes de solicitar saque'], 'status' => 422];
    }

    $lastWithdrawal = $db->fetch(
        'SELECT created_at FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1',
        [$userId]
    );

    if ($lastWithdrawal && $cooldownMinutes > 0) {
        $lastTime = strtotime($lastWithdrawal['created_at']);
        $cooldownEnd = $lastTime + ($cooldownMinutes * 60);
        if (time() < $cooldownEnd) {
            $waitMinutes = ceil(($cooldownEnd - time()) / 60);
            return ['body' => ['success' => false, 'error' => 'withdrawal_cooldown', 'message' => 'Aguarde ' . $waitMinutes . ' minuto(s) para novo saque'], 'status' => 429];
        }
    }

    try {
        $db->beginTransaction();

        $db->execute(
            'UPDATE users SET current_balance = current_balance - ?, updated_at = datetime(\'now\') WHERE id = ? AND current_balance >= ?',
            [$amount, $userId, $amount]
        );

        $pixKey = $user['pix_key'];
        $pixKeyType = $user['pix_key_type'] ?: 'cpf';

        $asaas = new AsaasService($db);
        $transferResult = $asaas->createTransfer($amount, $pixKey, $pixKeyType, 'AstraPay - Saque manual');

        $feeAmount = $transferResult['fee'] ?? 0;
        $netAmount = $amount - $feeAmount;

        $db->execute(
            'INSERT INTO withdrawals (user_id, amount, net_amount, fee_amount, pix_key, pix_key_type,
             asaas_transfer_id, status, completed_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime(\'now\'), datetime(\'now\'), datetime(\'now\'))',
            [$userId, $amount, $netAmount, $feeAmount, $pixKey, $pixKeyType, $transferResult['transfer_id'], $transferResult['status']]
        );

        $withdrawalId = $db->lastInsertId();

        if ($transferResult['status'] === 'completed') {
            $db->execute('UPDATE users SET total_withdrawn = total_withdrawn + ?, updated_at = datetime(\'now\') WHERE id = ?', [$amount, $userId]);
        }

        if ($transferResult['status'] === 'failed') {
            $db->execute('UPDATE users SET current_balance = current_balance + ?, updated_at = datetime(\'now\') WHERE id = ?', [$amount, $userId]);
            $db->execute("UPDATE withdrawals SET status = 'failed', updated_at = datetime('now') WHERE id = ?", [$withdrawalId]);
        }

        $db->commit();

        return [
            'body' => ['success' => true, 'data' => ['withdrawal' => [
                'id'               => (int) $withdrawalId,
                'amount'           => $amount,
                'net_amount'       => $netAmount,
                'fee_amount'       => $feeAmount,
                'status'           => $transferResult['status'],
                'asaas_transfer_id' => $transferResult['transfer_id'],
                'created_at'       => date('c'),
            ]]],
            'status' => 201,
        ];

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollback();
            $db->execute('UPDATE users SET current_balance = current_balance + ?, updated_at = datetime(\'now\') WHERE id = ?', [$amount, $userId]);
        }
        error_log('[AstraPay] Withdrawal request failed: ' . $e->getMessage());
        return ['body' => ['success' => false, 'error' => 'asaas_error', 'message' => 'Erro ao processar saque: ' . $e->getMessage()], 'status' => 503];
    }
}

function handle_withdraw_history($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));

    $total = (int) ($db->fetch('SELECT COUNT(*) as count FROM withdrawals WHERE user_id = ?', [$userId])['count'] ?? 0);
    $totalPages = ceil($total / $limit);
    $offset = ($page - 1) * $limit;

    $withdrawals = $db->fetchAll(
        'SELECT id, amount, net_amount, fee_amount, pix_key, pix_key_type, asaas_transfer_id,
                status, error_message, retry_count, completed_at, created_at
         FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
        [$userId, $limit, $offset]
    );

    $stats = $db->fetch(
        'SELECT COALESCE(SUM(amount), 0) as total_amount, COUNT(*) as total_count,
                COALESCE(SUM(CASE WHEN status = \'completed\' THEN amount ELSE 0 END), 0) as completed_amount
         FROM withdrawals WHERE user_id = ?',
        [$userId]
    );

    return [
        'body' => [
            'success'    => true,
            'data'       => $withdrawals,
            'stats'      => [
                'total_amount'     => (float) ($stats['total_amount'] ?? 0),
                'total_count'      => (int) ($stats['total_count'] ?? 0),
                'completed_amount' => (float) ($stats['completed_amount'] ?? 0),
            ],
            'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $totalPages],
        ],
        'status' => 200,
    ];
}
