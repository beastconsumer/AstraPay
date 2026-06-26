<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/pix_api.php';
require_once __DIR__ . '/webhook.php';
require_once __DIR__ . '/withdraw_api.php';
require_once __DIR__ . '/stats_api.php';
require_once __DIR__ . '/admin_api.php';
require_once __DIR__ . '/public_api.php';
require_once __DIR__ . '/api_keys.php';

function routerHandleRequest(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-TOKEN, X-Requested-With, X-Api-Key');
    header('Access-Control-Max-Age: 86400');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');

    $method = strtoupper($_SERVER['REQUEST_METHOD']);
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = rtrim($uri, '/');
    if ($uri === '') $uri = '/';

    if ($method === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $input = [];
    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $raw = file_get_contents('php://input');
        if ($raw !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) $input = $decoded;
        }
        if (empty($input)) $input = $_POST;
    }

    try {
        if (strpos($uri, '/api/v1/') === 0) {
            handlePublicApi();
            return;
        }

        if (strpos($uri, '/api/keys') === 0) {
            handleApiKeysRoutes();
            return;
        }

        if (strpos($uri, '/api/admin') === 0) {
            adminApiHandleRequest($method, $uri);
            return;
        }

        $routesExact = [
            'POST /api/auth/register'         => 'handleRegister',
            'POST /api/auth/login'             => 'handleLogin',
            'POST /api/auth/verify-email'      => 'handleVerifyEmail',
            'GET /api/auth/me'                 => 'handleMe',
            'POST /api/auth/send-verification' => 'handleSendVerification',
            'POST /api/pix/create'             => 'handle_pix_create',
            'GET /api/pix/status'             => 'handle_pix_status',
            'GET /api/pix/list'               => 'handle_pix_list',
            'GET /api/pix/stats'              => 'handle_pix_stats',
            'POST /api/pix/cancel'            => 'handle_pix_cancel',
            'POST /api/webhook/asaas'          => 'handle_webhook_asaas',
            'POST /api/withdraw/request'       => 'handle_withdraw_request',
            'GET /api/withdraw/history'        => 'handle_withdraw_history',
            'GET /api/user/stats'             => 'handle_user_stats',
            'GET /api/user/dashboard'          => 'handle_user_dashboard',
        ];

        $routeKey = "$method $uri";
        if (isset($routesExact[$routeKey])) {
            $handler = $routesExact[$routeKey];
            if (!function_exists($handler)) {
                jsonResponse(['success' => false, 'error' => 'server_error', 'message' => 'Handler not found'], 500);
            }
            $result = $handler($input);
            jsonResponse($result['body'], $result['status']);
        }

        jsonResponse(['success' => false, 'error' => 'not_found', 'message' => "Route not found: $method $uri"], 404);
    } catch (PDOException $e) {
        error_log('[AstraPay DB Error] ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'server_error', 'message' => 'Internal server error'], 500);
    } catch (Exception $e) {
        error_log('[AstraPay Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        jsonResponse(['success' => false, 'error' => 'server_error', 'message' => 'Internal server error'], 500);
    }
}
