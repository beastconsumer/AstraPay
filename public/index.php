<?php
session_start();

$request_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$request_uri = rtrim($request_uri, '/') ?: '/';

// Serve static files directly (PHP built-in server)
if ($request_uri !== '/' && preg_match('/\.\w+$/', $request_uri)) {
    return false;
}

if (strpos($request_uri, '/admin') === 0) {
    require_once __DIR__ . '/../backend/public/index.php';
    exit;
}

if (strpos($request_uri, '/api/') === 0) {
    require_once __DIR__ . '/../backend/config.php';
    require_once __DIR__ . '/../backend/db.php';
    require_once __DIR__ . '/../backend/middleware.php';
    require_once __DIR__ . '/../backend/router.php';
    routerHandleRequest();
    exit;
}

$routes = [
    '/'              => ['page' => 'landing',      'layout' => 'public', 'title' => 'AstraPay - Pagamentos PIX Instantaneos'],
    '/login'         => ['page' => 'login',         'layout' => 'auth',   'title' => 'Entrar - AstraPay'],
    '/register'      => ['page' => 'register',      'layout' => 'auth',   'title' => 'Criar Conta - AstraPay'],
    '/verify-email'  => ['page' => 'verify-email',  'layout' => 'auth',   'title' => 'Verificar Email - AstraPay'],
    '/dashboard'     => ['page' => 'dashboard',     'layout' => 'app',    'title' => 'Dashboard - AstraPay'],
    '/pix'           => ['page' => 'pix',           'layout' => 'app',    'title' => 'Gerar PIX - AstraPay'],
    '/transactions'  => ['page' => 'transactions',  'layout' => 'app',    'title' => 'Transacoes - AstraPay'],
    '/settings'      => ['page' => 'settings',      'layout' => 'app',    'title' => 'Configuracoes - AstraPay'],
    '/api-docs'      => ['page' => 'api-docs',      'layout' => 'public', 'title' => 'API Docs - AstraPay'],
    '/api-keys'      => ['page' => 'api-keys',      'layout' => 'app',    'title' => 'API Keys - AstraPay'],
];

$route = $routes[$request_uri] ?? null;

if (!$route) {
    http_response_code(404);
    $route = $routes['/'];
}

$page          = $route['page'];
$layout        = $route['layout'];
$page_title    = $route['title'];
$requires_auth = $layout === 'app';

define('CURRENT_PAGE', $page);

ob_start();
include __DIR__ . '/templates/layout.php';
$output = ob_get_clean();
echo $output;
