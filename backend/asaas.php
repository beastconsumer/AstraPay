<?php

define('ASAAS_API_URL', 'https://api.asaas.com/v3');
define('ASAAS_WEBHOOK_SECRET', '');
define('ASAAS_MAX_RETRIES', 3);
define('ASAAS_RETRY_DELAY_MS', 1000);
define('PIX_EXPIRATION_MINUTES', 30);
define('LOG_DIR', __DIR__ . '/../data/logs');

class AsaasService
{
    private $db;
    private $apiKey;
    private $apiUrl;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::getInstance();
        $this->apiKey = ASAAS_API_KEY;
        $this->apiUrl = ASAAS_API_URL;
    }

    private function log($level, $message, $data = null)
    {
        $dir = LOG_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $ts = date('Y-m-d H:i:s');
        $entry = "[{$ts}] [{$level}] [Asaas] {$message}";
        if ($data !== null) {
            $entry .= ' | ' . json_encode($data, JSON_UNESCAPED_SLASHES);
        }
        $entry .= PHP_EOL;
        @file_put_contents($dir . '/asaas.log', $entry, FILE_APPEND | LOCK_EX);
    }

    public function createPayment($userId, $amount, $description = '', $externalRef = '', $payerName = '', $payerCpfCnpj = '', $payerEmail = '')
    {
        $amount = round((float) $amount, 2);
        $dueDate = date('Y-m-d');
        $description = $description ?: 'AstraPay - Pagamento PIX';
        $externalRef = $externalRef ?: ('astrapay_user_' . $userId . '_' . time());

        $body = [
            'billingType'        => 'PIX',
            'value'              => $amount,
            'dueDate'            => $dueDate,
            'description'        => mb_substr($description, 0, 500),
            'externalReference'  => $externalRef,
            'postalService'      => false,
        ];

        $cpfClean = '';
        if ($payerCpfCnpj) {
            $cpfClean = preg_replace('/[^0-9]/', '', $payerCpfCnpj);
            if (strlen($cpfClean) === 11) {
                $body['customer'] = $cpfClean;
            }
        }
        if ($payerName && $cpfClean) {
            $body['customer'] = $payerName;
            $body['cpfCnpj'] = $cpfClean;
        }
        if ($payerEmail) {
            $body['email'] = $payerEmail;
        }

        $result = $this->_request('POST', '/payments', $body);

        $this->log('INFO', 'createPayment', [
            'user_id'  => $userId,
            'amount'   => $amount,
            'asaas_id' => $result['id'] ?? null,
            'status'   => $result['status'] ?? null,
        ]);

        $asaasId = $result['id'] ?? null;
        $qrCodeUrl = $result['pixQrCodeUrl'] ?? $result['invoiceUrl'] ?? null;
        $copyPaste = $result['payload'] ?? $result['pixCopiaECola'] ?? null;
        $asaasStatus = strtolower($result['status'] ?? 'PENDING');

        $statusMap = [
            'pending'   => 'pending',
            'confirmed' => 'confirmed',
            'received'  => 'confirmed',
            'refunded'  => 'refunded',
            'cancelled' => 'cancelled',
            'overdue'   => 'cancelled',
            'active'    => 'pending',
        ];
        $ourStatus = $statusMap[$asaasStatus] ?? 'pending';

        $user = $this->db->fetch('SELECT admin_fee_pct FROM users WHERE id = ?', [$userId]);
        $feePercent = $user ? (float) $user['admin_fee_pct'] : 0;
        $feeAmount = round($amount * $feePercent / 100, 2);
        $netAmount = round($amount - $feeAmount, 2);

        $this->db->execute(
            'INSERT INTO transactions (user_id, asaas_payment_id, external_ref, amount, net_amount, fee_amount, fee_percent,
             status, payer_name, payer_cpf_cnpj, payer_email, description, pix_copy_paste, pix_qrcode_url,
             pix_expiration, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime(\'now\', \'+? minutes\'), datetime(\'now\'), datetime(\'now\'))',
            [
                $userId, $asaasId, $externalRef, $amount, $netAmount, $feeAmount, $feePercent,
                $ourStatus, $payerName ?: null, $payerCpfCnpj ?: null, $payerEmail ?: null,
                $description, $copyPaste, $qrCodeUrl, PIX_EXPIRATION_MINUTES,
            ]
        );

        $txId = $this->db->lastInsertId();

        return [
            'id'             => (int) $txId,
            'asaas_id'       => $asaasId,
            'amount'         => $amount,
            'net_amount'     => $netAmount,
            'fee_amount'     => $feeAmount,
            'fee_percent'    => $feePercent,
            'status'         => $ourStatus,
            'qr_code'        => $qrCodeUrl,
            'copy_paste'     => $copyPaste,
            'pix_expiration' => date('c', strtotime('+' . PIX_EXPIRATION_MINUTES . ' minutes')),
            'description'    => $description,
            'external_ref'   => $externalRef,
        ];
    }

    public function getPaymentStatus($asaasId)
    {
        $result = $this->_request('GET', '/payments/' . $asaasId);

        $this->log('INFO', 'getPaymentStatus', [
            'asaas_id' => $asaasId,
            'status'   => $result['status'] ?? null,
        ]);

        $statusMap = [
            'PENDING'   => 'pending',
            'CONFIRMED' => 'confirmed',
            'RECEIVED'  => 'received',
            'REFUNDED'  => 'refunded',
            'CANCELLED' => 'cancelled',
            'OVERDUE'   => 'cancelled',
        ];

        return [
            'asaas_id'      => $result['id'] ?? $asaasId,
            'status'        => $statusMap[$result['status'] ?? ''] ?? 'pending',
            'asaas_status'  => $result['status'] ?? 'UNKNOWN',
            'value'         => (float) ($result['value'] ?? 0),
            'net_value'     => (float) ($result['netValue'] ?? 0),
            'original_data' => $result,
        ];
    }

    public function createTransfer($value, $pixKey, $pixKeyType, $description = '')
    {
        $value = round((float) $value, 2);

        $typeMap = [
            'cpf'    => 'CPF',
            'cnpj'   => 'CNPJ',
            'email'  => 'EMAIL',
            'phone'  => 'PHONE',
            'random' => 'EVP',
            'evp'    => 'EVP',
        ];
        $asaasKeyType = $typeMap[strtolower($pixKeyType)] ?? 'CPF';

        $body = [
            'value'             => $value,
            'pixAddressKey'     => $pixKey,
            'pixAddressKeyType' => $asaasKeyType,
            'description'       => $description ?: 'AstraPay - Transferencia automatica',
        ];

        $result = $this->_request('POST', '/transfers', $body);

        $this->log('INFO', 'createTransfer', [
            'value'        => $value,
            'pix_key_type' => $pixKeyType,
            'transfer_id'  => $result['id'] ?? null,
            'status'       => $result['status'] ?? null,
        ]);

        $statusMap = [
            'PENDING'   => 'processing',
            'DONE'      => 'completed',
            'CANCELLED' => 'failed',
            'FAILED'    => 'failed',
            'SCHEDULED' => 'processing',
        ];

        return [
            'transfer_id'   => $result['id'] ?? null,
            'status'        => $statusMap[$result['status'] ?? ''] ?? 'processing',
            'asaas_status'  => $result['status'] ?? 'UNKNOWN',
            'value'         => $value,
            'fee'           => (float) ($result['transferFee'] ?? 0),
            'net_value'     => $value,
            'original_data' => $result,
        ];
    }

    public function getBalance()
    {
        $result = $this->_request('GET', '/finance/balance');

        return [
            'balance'         => (float) ($result['balance'] ?? 0),
            'blocked_balance' => (float) ($result['blockedBalance'] ?? 0),
            'net_balance'     => (float) ($result['netBalance'] ?? 0),
            'currency'        => $result['currency'] ?? 'BRL',
        ];
    }

    private function _request($method, $endpoint, $body = null, $retryCount = 0)
    {
        $url = $this->apiUrl . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'access_token: ' . $this->apiKey,
                'User-Agent: AstraPay/1.0',
            ],
        ]);

        if ($method === 'GET' && $body !== null) {
            $url .= '?' . http_build_query($body);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }

        if ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($curlErrno || ($httpCode >= 500 && $httpCode < 600)) {
            if ($retryCount < ASAAS_MAX_RETRIES) {
                $delayMs = ASAAS_RETRY_DELAY_MS * pow(2, $retryCount);
                usleep($delayMs * 1000);
                $this->log('WARNING', 'request retry', [
                    'method'      => $method,
                    'endpoint'    => $endpoint,
                    'retry_count' => $retryCount + 1,
                    'http_code'   => $httpCode,
                ]);
                return $this->_request($method, $endpoint, $body, $retryCount + 1);
            }
            $this->log('ERROR', 'request failed after retries', [
                'method'    => $method,
                'endpoint'  => $endpoint,
                'http_code' => $httpCode,
                'error'     => $curlError ?: 'HTTP ' . $httpCode,
            ]);
            throw new Exception('Asaas API unreachable: ' . ($curlError ?: 'HTTP ' . $httpCode));
        }

        if ($httpCode === 401) {
            $this->log('CRITICAL', 'API key invalid', ['http_code' => 401]);
            throw new Exception('Asaas authentication failed. Check API key.');
        }

        if ($httpCode === 429) {
            if ($retryCount < ASAAS_MAX_RETRIES) {
                $delayMs = max(ASAAS_RETRY_DELAY_MS * pow(2, $retryCount + 1), 5000);
                usleep($delayMs * 1000);
                $this->log('WARNING', 'rate limit retry', ['retry_count' => $retryCount + 1]);
                return $this->_request($method, $endpoint, $body, $retryCount + 1);
            }
            throw new Exception('Asaas rate limit exceeded');
        }

        if ($httpCode >= 400) {
            $errorMsg = $data['errors'][0]['description'] ?? ($data['message'] ?? 'Unknown error');
            $this->log('ERROR', 'API error', [
                'method'    => $method,
                'endpoint'  => $endpoint,
                'http_code' => $httpCode,
                'response'  => $data,
            ]);
            throw new Exception('Asaas API error (' . $httpCode . '): ' . $errorMsg);
        }

        if (!is_array($data)) {
            $this->log('ERROR', 'invalid response', [
                'method'   => $method,
                'endpoint' => $endpoint,
                'response' => substr($response, 0, 500),
            ]);
            throw new Exception('Invalid response from Asaas API');
        }

        return $data;
    }
}
