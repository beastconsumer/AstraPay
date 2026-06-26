<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/asaas.php';

function handle_pix_create($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $amount = isset($input['amount']) ? (float) $input['amount'] : 0;
    $description = trim($input['description'] ?? '');
    $payerName = trim($input['payer_name'] ?? '');
    $payerCpfCnpj = trim($input['payer_cpf_cnpj'] ?? '');
    $payerEmail = trim($input['payer_email'] ?? '');
    $externalRef = trim($input['external_ref'] ?? '');

    if ($amount <= 0) {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'Valor deve ser maior que zero'], 'status' => 422];
    }
    if ($amount < 0.50) {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'Valor minimo: R$ 0,50'], 'status' => 422];
    }
    if ($amount > 100000) {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'Valor maximo: R$ 100.000,00'], 'status' => 422];
    }

    $perTxLimit = (float) ($user['per_tx_limit'] ?? 100);
    if ($amount > $perTxLimit) {
        return ['body' => ['success' => false, 'error' => 'limit_exceeded_per_tx', 'message' => 'Valor excede seu limite por transacao (R$ ' . number_format($perTxLimit, 2, ',', '.') . ')'], 'status' => 422];
    }

    $dailyVolume = (float) ($db->fetch(
        "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND status NOT IN ('cancelled', 'refunded') AND date(created_at) = date('now')",
        [$userId]
    )['total'] ?? 0);

    $dailyLimit = (float) ($user['daily_limit'] ?? 100);
    if (($dailyVolume + $amount) > $dailyLimit) {
        return ['body' => ['success' => false, 'error' => 'limit_exceeded_daily', 'message' => 'Limite diario excedido (R$ ' . number_format($dailyLimit, 2, ',', '.') . ')'], 'status' => 422];
    }

    $monthlyVolume = (float) ($db->fetch(
        "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND status NOT IN ('cancelled', 'refunded') AND strftime('%Y-%m', created_at) = strftime('%Y-%m', 'now')",
        [$userId]
    )['total'] ?? 0);

    $monthlyLimit = (float) ($user['monthly_limit'] ?? 500);
    if (($monthlyVolume + $amount) > $monthlyLimit) {
        return ['body' => ['success' => false, 'error' => 'limit_exceeded_monthly', 'message' => 'Limite mensal excedido (R$ ' . number_format($monthlyLimit, 2, ',', '.') . ')'], 'status' => 422];
    }

    $holdAmount = (float) ($db->fetch("SELECT value FROM settings WHERE key = 'fraud_auto_hold_amount'")['value'] ?? 5000);
    $shouldHold = false;
    $holdReason = null;

    if ($amount >= $holdAmount) {
        $shouldHold = true;
        $holdReason = 'Valor acima do limite de retencao automatica (R$ ' . number_format($holdAmount, 2, ',', '.') . ')';
    }
    if ($user['tier'] === 'new' && $amount > 200) {
        $shouldHold = true;
        $holdReason = 'Conta nova com valor suspeito';
    }

    try {
        $asaas = new AsaasService($db);
        $result = $asaas->createPayment($userId, $amount, $description, $externalRef, $payerName, $payerCpfCnpj, $payerEmail);

        if ($shouldHold) {
            $db->execute(
                'UPDATE transactions SET held = 1, hold_reason = ?, status = ? WHERE id = ?',
                [$holdReason, 'held', $result['id']]
            );
            $result['status'] = 'held';
            $result['hold_reason'] = $holdReason;
        }

        return [
            'body'   => ['success' => true, 'data' => ['transaction' => $result]],
            'status' => 201,
        ];
    } catch (Exception $e) {
        error_log('[AstraPay] PIX create failed: ' . $e->getMessage());
        return ['body' => ['success' => false, 'error' => 'asaas_error', 'message' => 'Erro ao gerar PIX: ' . $e->getMessage()], 'status' => 503];
    }
}

function handle_pix_status($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $txId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($input['id'] ?? 0);
    if ($txId <= 0) {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'ID da transacao invalido'], 'status' => 422];
    }

    $transaction = $db->fetch('SELECT * FROM transactions WHERE id = ? AND user_id = ?', [$txId, $userId]);

    if (!$transaction) {
        return ['body' => ['success' => false, 'error' => 'not_found', 'message' => 'Transacao nao encontrada'], 'status' => 404];
    }

    if ($transaction['asaas_payment_id'] && $transaction['status'] === 'pending') {
        try {
            $asaas = new AsaasService($db);
            $freshStatus = $asaas->getPaymentStatus($transaction['asaas_payment_id']);
            $newStatus = $freshStatus['status'];
            if ($newStatus !== $transaction['status'] && in_array($newStatus, ['confirmed', 'received'])) {
                $db->execute("UPDATE transactions SET status = ?, updated_at = datetime('now') WHERE id = ?", [$newStatus, $txId]);
                $transaction['status'] = $newStatus;
                if ($newStatus === 'confirmed') {
                    process_tx_confirmation($db, $txId);
                }
            }
        } catch (Exception $e) {
            error_log('[AstraPay] Status refresh failed: ' . $e->getMessage());
        }
    }

    return [
        'body' => ['success' => true, 'data' => [
            'id'             => (int) $transaction['id'],
            'asaas_id'       => $transaction['asaas_payment_id'],
            'amount'         => (float) $transaction['amount'],
            'net_amount'     => (float) $transaction['net_amount'],
            'fee_amount'     => (float) $transaction['fee_amount'],
            'status'         => $transaction['status'],
            'held'           => (bool) ($transaction['held'] ?? false),
            'hold_reason'    => $transaction['hold_reason'],
            'description'    => $transaction['description'],
            'payer_name'     => $transaction['payer_name'],
            'pix_copy_paste' => $transaction['pix_copy_paste'],
            'pix_qrcode_url' => $transaction['pix_qrcode_url'],
            'pix_expiration' => $transaction['pix_expiration'],
            'created_at'     => $transaction['created_at'],
            'updated_at'     => $transaction['updated_at'],
        ]],
        'status' => 200,
    ];
}

function handle_pix_list($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));
    $status = isset($_GET['status']) ? trim($_GET['status']) : null;
    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : null;
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;

    $where = 'WHERE user_id = ?';
    $params = [$userId];

    if ($status && in_array($status, ['pending', 'confirmed', 'received', 'refunded', 'cancelled', 'held'])) {
        $where .= ' AND status = ?';
        $params[] = $status;
    }
    if ($dateFrom) {
        $where .= ' AND date(created_at) >= ?';
        $params[] = $dateFrom;
    }
    if ($dateTo) {
        $where .= ' AND date(created_at) <= ?';
        $params[] = $dateTo;
    }
    if ($search) {
        $where .= ' AND (description LIKE ? OR payer_name LIKE ? OR external_ref LIKE ?)';
        $s = '%' . $search . '%';
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }

    $total = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions {$where}", $params)['count'] ?? 0);
    $totalPages = ceil($total / $limit);
    $offset = ($page - 1) * $limit;

    $transactions = $db->fetchAll(
        "SELECT id, asaas_payment_id, external_ref, amount, net_amount, fee_amount, fee_percent, status, held,
                payer_name, description, pix_copy_paste, pix_qrcode_url, pix_expiration, created_at, updated_at
         FROM transactions {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
        array_merge($params, [$limit, $offset])
    );

    return [
        'body' => [
            'success'    => true,
            'data'       => $transactions,
            'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'total_pages' => $totalPages],
        ],
        'status' => 200,
    ];
}

function handle_pix_stats($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $totalGenerated = (int) ($db->fetch('SELECT COUNT(*) as count FROM transactions WHERE user_id = ?', [$userId])['count'] ?? 0);
    $totalPaid = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status IN ('confirmed', 'received', 'withdrawn')", [$userId])['count'] ?? 0);
    $totalPending = (int) ($db->fetch("SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status = 'pending'", [$userId])['count'] ?? 0);
    $totalVolume = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND status IN ('confirmed', 'received', 'withdrawn')", [$userId])['total'] ?? 0);
    $grossToday = (float) ($db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE user_id = ? AND date(created_at) = date('now')", [$userId])['total'] ?? 0);
    $netToday = (float) ($db->fetch("SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions WHERE user_id = ? AND date(created_at) = date('now')", [$userId])['total'] ?? 0);
    $feesToday = (float) ($db->fetch("SELECT COALESCE(SUM(fee_amount), 0) as total FROM transactions WHERE user_id = ? AND date(created_at) = date('now')", [$userId])['total'] ?? 0);

    return [
        'body' => ['success' => true, 'data' => [
            'total_generated' => $totalGenerated,
            'total_paid'      => $totalPaid,
            'total_pending'   => $totalPending,
            'total_volume'    => round($totalVolume, 2),
            'gross_today'     => round($grossToday, 2),
            'net_today'       => round($netToday, 2),
            'fees_today'      => round($feesToday, 2),
        ]],
        'status' => 200,
    ];
}

function handle_pix_cancel($input)
{
    $user = auth();
    $userId = $user['id'];
    $db = DB::getInstance();

    $txId = (int) ($input['id'] ?? 0);
    if ($txId <= 0) {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'ID da transacao invalido'], 'status' => 422];
    }

    $transaction = $db->fetch('SELECT * FROM transactions WHERE id = ? AND user_id = ?', [$txId, $userId]);
    if (!$transaction) {
        return ['body' => ['success' => false, 'error' => 'not_found', 'message' => 'Transacao nao encontrada'], 'status' => 404];
    }
    if ($transaction['status'] !== 'pending') {
        return ['body' => ['success' => false, 'error' => 'validation_error', 'message' => 'Apenas transacoes pendentes podem ser canceladas'], 'status' => 422];
    }

    $db->execute("UPDATE transactions SET status = 'cancelled', updated_at = datetime('now') WHERE id = ?", [$txId]);

    return ['body' => ['success' => true, 'data' => ['message' => 'Transacao cancelada com sucesso']], 'status' => 200];
}

function process_tx_confirmation($db, $txId)
{
    require_once __DIR__ . '/withdraw_processor.php';
    $processor = new WithdrawalProcessor($db);
    $processor->processTransaction($txId);
}
