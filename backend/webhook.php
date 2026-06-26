<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/asaas.php';

function handle_webhook_asaas($input)
{
    $db = DB::getInstance();

    $rawPayload = file_get_contents('php://input');
    $payload = json_decode($rawPayload, true);

    $webhookSecret = $db->fetch("SELECT value FROM settings WHERE key = 'asaas_webhook_secret'");
    $expectedSecret = $webhookSecret ? $webhookSecret['value'] : ASAAS_WEBHOOK_SECRET;
    $receivedSecret = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? '';

    $logDir = LOG_DIR;
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }
    error_log('[AstraPay Webhook] ' . date('Y-m-d H:i:s') . ' | event=' . ($payload['event'] ?? 'unknown') . ' | has_secret=' . (!empty($receivedSecret) ? 'yes' : 'no'));

    if (!empty($expectedSecret) && !hash_equals($expectedSecret, $receivedSecret)) {
        error_log('[AstraPay Webhook] Invalid signature');
        return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
    }

    if (!$payload || !is_array($payload)) {
        error_log('[AstraPay Webhook] Invalid payload');
        return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
    }

    $event = $payload['event'] ?? '';
    $payment = $payload['payment'] ?? [];

    if (!$event || !$payment) {
        return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
    }

    $asaasPaymentId = $payment['id'] ?? '';
    $asaasStatus = $payment['status'] ?? '';
    $externalRef = $payment['externalReference'] ?? '';

    if (!$asaasPaymentId) {
        return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
    }

    $transaction = $db->fetch(
        "SELECT t.*, u.pix_key, u.pix_key_type, u.current_balance, u.total_received
         FROM transactions t JOIN users u ON u.id = t.user_id
         WHERE t.asaas_payment_id = ?",
        [$asaasPaymentId]
    );

    if (!$transaction && $externalRef) {
        $transaction = $db->fetch(
            "SELECT t.*, u.pix_key, u.pix_key_type, u.current_balance, u.total_received
             FROM transactions t JOIN users u ON u.id = t.user_id
             WHERE t.external_ref = ?",
            [$externalRef]
        );
    }

    if (!$transaction) {
        error_log('[AstraPay Webhook] TX not found: ' . $asaasPaymentId);
        return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
    }

    $txId = $transaction['id'];
    $userId = $transaction['user_id'];
    $currentStatus = $transaction['status'];
    $held = (int) ($transaction['held'] ?? 0);

    $eventStatusMap = [
        'PAYMENT_CONFIRMED' => 'confirmed',
        'PAYMENT_RECEIVED'  => 'received',
        'PAYMENT_REFUNDED'  => 'refunded',
        'PAYMENT_OVERDUE'   => 'cancelled',
        'PAYMENT_DELETED'   => 'cancelled',
        'PAYMENT_CANCELLED' => 'cancelled',
    ];

    $newStatus = $eventStatusMap[$event] ?? null;

    if (!$newStatus) {
        return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
    }

    if ($currentStatus === $newStatus) {
        return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
    }

    if ($held && in_array($newStatus, ['confirmed', 'received'])) {
        $newStatus = 'held';
    }

    try {
        $db->beginTransaction();

        $db->execute(
            "UPDATE transactions SET status = ?, webhook_received_at = datetime('now'), updated_at = datetime('now') WHERE id = ?",
            [$newStatus, $txId]
        );

        if (in_array($newStatus, ['confirmed', 'received']) && !$held) {
            $amount = (float) $transaction['amount'];
            $feePercent = (float) $transaction['fee_percent'];
            $feeAmount = round($amount * $feePercent / 100, 2);
            $netAmount = round($amount - $feeAmount, 2);

            $db->execute('UPDATE transactions SET net_amount = ?, fee_amount = ? WHERE id = ?', [$netAmount, $feeAmount, $txId]);
            $db->execute(
                'UPDATE users SET total_received = total_received + ?, current_balance = current_balance + ?, updated_at = datetime(\'now\') WHERE id = ?',
                [$amount, $netAmount, $userId]
            );
        }

        $db->commit();

        if (in_array($newStatus, ['confirmed', 'received']) && !$held) {
            try {
                require_once __DIR__ . '/withdraw_processor.php';
                $processor = new WithdrawalProcessor($db);
                $processor->processTransaction($txId);
            } catch (Exception $e) {
                error_log('[AstraPay] Auto-withdrawal after webhook failed: ' . $e->getMessage());
            }
        }

        error_log('[AstraPay Webhook] Processed: tx=' . $txId . ' user=' . $userId . ' event=' . $event . ' status=' . $newStatus);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollback();
        }
        error_log('[AstraPay Webhook] Error: ' . $e->getMessage());
    }

    return ['body' => ['success' => true, 'message' => 'OK'], 'status' => 200];
}
