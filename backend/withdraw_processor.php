<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/asaas.php';

class WithdrawalProcessor
{
    private $db;
    private $asaas;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::getInstance();
        $this->asaas = new AsaasService($this->db);
    }

    private function log($level, $message, $data = null)
    {
        $dir = LOG_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $ts = date('Y-m-d H:i:s');
        $entry = "[{$ts}] [{$level}] [Withdraw] {$message}";
        if ($data !== null) {
            $entry .= ' | ' . json_encode($data, JSON_UNESCAPED_SLASHES);
        }
        $entry .= PHP_EOL;
        @file_put_contents($dir . '/withdrawals.log', $entry, FILE_APPEND | LOCK_EX);
    }

    public function processTransaction($txId)
    {
        $transaction = $this->db->fetch(
            "SELECT t.*, u.pix_key, u.pix_key_type, u.banned, u.id as user_id_confirm
             FROM transactions t JOIN users u ON u.id = t.user_id
             WHERE t.id = ? AND t.status IN ('confirmed', 'received') AND t.held = 0",
            [$txId]
        );

        if (!$transaction) {
            $this->log('INFO', 'tx not eligible', ['tx_id' => $txId]);
            return ['success' => false, 'error' => 'Transaction not eligible'];
        }

        $userId = $transaction['user_id'];

        if ($transaction['banned']) {
            $this->log('WARNING', 'user banned', ['user_id' => $userId, 'tx_id' => $txId]);
            return ['success' => false, 'error' => 'User is banned'];
        }

        $existing = $this->db->fetch('SELECT id FROM withdrawals WHERE transaction_id = ?', [$txId]);
        if ($existing) {
            return ['success' => false, 'error' => 'Withdrawal already processed'];
        }

        if (empty($transaction['pix_key'])) {
            $this->db->execute(
                "UPDATE transactions SET error_message = 'Usuario sem chave PIX configurada', updated_at = datetime('now') WHERE id = ?",
                [$txId]
            );
            return ['success' => false, 'error' => 'User has no PIX key'];
        }

        $grossAmount = (float) $transaction['amount'];
        $feePercent = (float) $transaction['fee_percent'];
        $feeAmount = round($grossAmount * $feePercent / 100, 2);
        $netAmount = round($grossAmount - $feeAmount, 2);

        $this->db->execute('UPDATE transactions SET net_amount = ?, fee_amount = ?, updated_at = datetime(\'now\') WHERE id = ?', [$netAmount, $feeAmount, $txId]);

        if ($netAmount <= 0) {
            return ['success' => false, 'error' => 'Net amount zero after fees'];
        }

        if ($netAmount < 1.00) {
            $this->db->execute(
                "UPDATE transactions SET error_message = 'Valor liquido abaixo do minimo para transferencia (R$ 1,00)', updated_at = datetime('now') WHERE id = ?",
                [$txId]
            );
            return ['success' => false, 'error' => 'Below Asaas minimum'];
        }

        $pixKey = $transaction['pix_key'];
        $pixKeyType = $transaction['pix_key_type'] ?: 'cpf';
        $description = 'AstraPay - Recebimento #' . $txId;

        try {
            $this->db->beginTransaction();

            $this->db->execute(
                'INSERT INTO withdrawals (user_id, transaction_id, amount, net_amount, fee_amount, pix_key, pix_key_type, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, \'processing\', datetime(\'now\'), datetime(\'now\'))',
                [$userId, $txId, $netAmount, $netAmount, 0, $pixKey, $pixKeyType]
            );

            $withdrawalId = $this->db->lastInsertId();
            $this->db->commit();

            $transferResult = $this->asaas->createTransfer($netAmount, $pixKey, $pixKeyType, $description);

            $this->db->beginTransaction();

            if ($transferResult['status'] === 'completed') {
                $this->db->execute(
                    "UPDATE withdrawals SET status = 'completed', asaas_transfer_id = ?, completed_at = datetime('now'), updated_at = datetime('now') WHERE id = ?",
                    [$transferResult['transfer_id'], $withdrawalId]
                );

                $this->db->execute(
                    'UPDATE users SET total_withdrawn = total_withdrawn + ?, current_balance = current_balance - ?, updated_at = datetime(\'now\') WHERE id = ? AND current_balance >= ?',
                    [$netAmount, $netAmount, $userId, $netAmount]
                );

                $this->db->execute("UPDATE transactions SET status = 'withdrawn', updated_at = datetime('now') WHERE id = ?", [$txId]);

                $this->log('INFO', 'transfer completed', ['tx_id' => $txId, 'wd_id' => $withdrawalId, 'net' => $netAmount]);
                $this->db->commit();

                return ['success' => true, 'withdrawal_id' => $withdrawalId, 'transfer_id' => $transferResult['transfer_id'], 'status' => 'completed'];

            } elseif ($transferResult['status'] === 'processing') {
                $this->db->execute(
                    "UPDATE withdrawals SET status = 'processing', asaas_transfer_id = ?, updated_at = datetime('now') WHERE id = ?",
                    [$transferResult['transfer_id'], $withdrawalId]
                );
                $this->log('INFO', 'transfer processing', ['tx_id' => $txId, 'wd_id' => $withdrawalId]);
                $this->db->commit();

                return ['success' => true, 'withdrawal_id' => $withdrawalId, 'transfer_id' => $transferResult['transfer_id'], 'status' => 'processing'];

            } else {
                $this->db->execute(
                    "UPDATE withdrawals SET status = 'failed', asaas_transfer_id = ?, error_message = ?, retry_count = 1, next_retry_at = datetime('now', '+5 minutes'), updated_at = datetime('now') WHERE id = ?",
                    [$transferResult['transfer_id'], 'Asaas status: ' . ($transferResult['asaas_status'] ?? 'UNKNOWN'), $withdrawalId]
                );
                $this->log('ERROR', 'transfer failed', ['tx_id' => $txId, 'wd_id' => $withdrawalId, 'asaas_status' => $transferResult['asaas_status']]);
                $this->db->commit();

                return ['success' => false, 'withdrawal_id' => $withdrawalId, 'error' => 'Asaas: ' . ($transferResult['asaas_status'] ?? 'UNKNOWN')];
            }

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            $errorMessage = $e->getMessage();

            try {
                $existingWd = $this->db->fetch('SELECT id FROM withdrawals WHERE transaction_id = ?', [$txId]);
                if ($existingWd) {
                    $this->db->execute(
                        "UPDATE withdrawals SET status = 'failed', error_message = ?, retry_count = 1, next_retry_at = datetime('now', '+5 minutes'), updated_at = datetime('now') WHERE id = ?",
                        [$errorMessage, $existingWd['id']]
                    );
                }
            } catch (Exception $inner) {
                $this->log('ERROR', 'error logging failure', ['error' => $inner->getMessage()]);
            }

            $this->log('ERROR', 'exception', ['tx_id' => $txId, 'error' => $errorMessage]);
            return ['success' => false, 'error' => $errorMessage];
        }
    }

    public function retryFailedWithdrawals()
    {
        $pending = $this->db->fetchAll(
            "SELECT * FROM withdrawals WHERE status = 'failed' AND retry_count < 3 AND next_retry_at <= datetime('now') ORDER BY next_retry_at ASC LIMIT 20"
        );

        $results = [];
        foreach ($pending as $wd) {
            $userId = $wd['user_id'];
            $pixKey = $wd['pix_key'];
            $pixKeyType = $wd['pix_key_type'];
            $netAmount = (float) $wd['net_amount'];
            $wdId = $wd['id'];

            try {
                $transferResult = $this->asaas->createTransfer($netAmount, $pixKey, $pixKeyType, 'AstraPay - Retentativa saque #' . $wdId);

                if ($transferResult['status'] === 'completed') {
                    $this->db->execute(
                        "UPDATE withdrawals SET status = 'completed', asaas_transfer_id = ?, completed_at = datetime('now'), updated_at = datetime('now') WHERE id = ?",
                        [$transferResult['transfer_id'], $wdId]
                    );
                    $this->db->execute('UPDATE users SET total_withdrawn = total_withdrawn + ? WHERE id = ?', [$netAmount, $userId]);
                    $results[] = ['wd_id' => $wdId, 'success' => true, 'status' => 'completed'];
                    $this->log('INFO', 'retry completed', ['wd_id' => $wdId]);
                } elseif ($transferResult['status'] === 'processing') {
                    $this->db->execute("UPDATE withdrawals SET status = 'processing', asaas_transfer_id = ?, updated_at = datetime('now') WHERE id = ?", [$transferResult['transfer_id'], $wdId]);
                    $results[] = ['wd_id' => $wdId, 'success' => true, 'status' => 'processing'];
                } else {
                    $retryCount = (int) $wd['retry_count'] + 1;
                    $backoffMap = [1 => '+5 minutes', 2 => '+15 minutes', 3 => '+60 minutes'];
                    $nextRetry = $backoffMap[$retryCount] ?? null;

                    if ($retryCount >= 3 || !$nextRetry) {
                        $this->db->execute(
                            "UPDATE withdrawals SET retry_count = ?, next_retry_at = NULL, error_message = 'Max retries exceeded', updated_at = datetime('now') WHERE id = ?",
                            [$retryCount, $wdId]
                        );
                        $this->log('CRITICAL', 'permanently failed', ['wd_id' => $wdId, 'user_id' => $userId]);
                    } else {
                        $this->db->execute(
                            "UPDATE withdrawals SET retry_count = ?, next_retry_at = datetime('now', ?), updated_at = datetime('now') WHERE id = ?",
                            [$retryCount, $nextRetry, $wdId]
                        );
                    }
                    $results[] = ['wd_id' => $wdId, 'success' => false, 'status' => 'failed'];
                }
            } catch (Exception $e) {
                $retryCount = (int) $wd['retry_count'] + 1;
                $backoffMap = [1 => '+5 minutes', 2 => '+15 minutes', 3 => '+60 minutes'];
                $nextRetry = $backoffMap[$retryCount] ?? null;

                if ($retryCount >= 3 || !$nextRetry) {
                    $this->db->execute(
                        "UPDATE withdrawals SET retry_count = ?, next_retry_at = NULL, error_message = ?, updated_at = datetime('now') WHERE id = ?",
                        [$retryCount, $e->getMessage(), $wdId]
                    );
                } else {
                    $this->db->execute(
                        "UPDATE withdrawals SET retry_count = ?, next_retry_at = datetime('now', ?), error_message = ?, updated_at = datetime('now') WHERE id = ?",
                        [$retryCount, $nextRetry, $e->getMessage(), $wdId]
                    );
                }
                $results[] = ['wd_id' => $wdId, 'success' => false, 'error' => $e->getMessage()];
                $this->log('ERROR', 'retry exception', ['wd_id' => $wdId, 'error' => $e->getMessage()]);
            }
        }

        return $results;
    }
}
