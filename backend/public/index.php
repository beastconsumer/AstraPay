<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../admin_auth.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

if ($uri === '/admin/login') {
    require_once __DIR__ . '/../../templates/pages/admin/login.php';
    exit;
}

if ($uri === '/admin/logout') {
    adminLogout();
    header('Location: ' . APP_URL . '/admin/login');
    exit;
}

requireAdminPageAuth();

$admin = getAdminById((int)$_SESSION['admin_id']);

$pageMap = [
    '/admin'              => ['dashboard',    'Dashboard'],
    '/admin/dashboard'    => ['dashboard',    'Dashboard'],
    '/admin/users'        => ['users',        'Usuarios'],
    '/admin/transactions' => ['transactions', 'Transacoes'],
    '/admin/audit'        => ['audit',        'Auditoria'],
    '/admin/settings'     => ['settings',     'Configuracoes'],
];

if (!isset($pageMap[$uri])) {
    http_response_code(404);
    echo '<!DOCTYPE html><html class="dark"><head><meta charset="UTF-8"><title>404 - AstraPay Admin</title>'
        . '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">'
        . '<script src="https://cdn.tailwindcss.com"></script>'
        . '<script>tailwind.config={darkMode:"class",theme:{extend:{colors:{zinc:{950:"#09090b"}},fontFamily:{sans:["Inter","system-ui","sans-serif"]}}}}</script>'
        . '</head><body class="bg-zinc-950 text-zinc-100 flex items-center justify-center min-h-screen font-[\'Inter\']">'
        . '<div class="text-center"><h1 class="text-6xl font-bold text-zinc-800">404</h1><p class="text-zinc-400 mt-2">Pagina nao encontrada</p>'
        . '<a href="' . APP_URL . '/admin" class="inline-block mt-6 px-4 py-2 bg-amber-500 text-zinc-950 rounded-md font-medium text-sm hover:bg-amber-400 transition-colors">Dashboard</a></div></body></html>';
    exit;
}

[$page, $title] = $pageMap[$uri];

$db = DB::getInstance();
$heldCount = (int)$db->fetch("SELECT COUNT(*) as c FROM transactions WHERE held = 1 AND status = 'held'")['c'];
$adminName = h($admin['username'] ?? 'Admin');
$adminRole = h($admin['role'] ?? '');

function h(?string $str): string { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function formatBRL(float $val): string { return 'R$ ' . number_format($val, 2, ',', '.'); }
function formatCPF(?string $cpf): string {
    if (!$cpf) return '-';
    $d = preg_replace('/[^0-9]/', '', $cpf);
    return strlen($d) === 11 ? substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2) : $cpf;
}

$navItems = [
    'dashboard'    => ['Dashboard',      'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1'],
    'users'        => ['Usuarios',       'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    'transactions' => ['Transacoes',     'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
    'audit'        => ['Auditoria',       'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    'settings'     => ['Configuracoes',   'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
];

ob_start();
require __DIR__ . '/../../templates/pages/admin/' . $page . '.php';
$content = ob_get_clean();

?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?> - AstraPay Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { zinc: { 950: '#09090b' } },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { scrollbar-width: thin; scrollbar-color: #27272a transparent; }
        *::-webkit-scrollbar { width: 6px; }
        *::-webkit-scrollbar-track { background: transparent; }
        *::-webkit-scrollbar-thumb { background-color: #27272a; border-radius: 3px; }
        body { font-family: 'Inter', system-ui, sans-serif; background-color: #09090b; }
        .font-mono, .tabular-nums { font-variant-numeric: tabular-nums; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="fixed left-0 top-0 h-full w-64 bg-zinc-900 border-r border-zinc-800 z-40 flex flex-col">
            <div class="px-6 py-5 border-b border-zinc-800">
                <a href="<?= APP_URL ?>/admin" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-zinc-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span class="text-lg font-semibold text-zinc-100 tracking-tight">AstraPay</span>
                </a>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= APP_URL ?>/admin<?= $key === 'dashboard' ? '' : '/' . $key ?>" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm <?= $page === $key ? 'bg-amber-500/10 text-amber-400 font-medium border border-amber-500/20' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100' ?> transition-colors">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item[1] ?>"/></svg>
                    <?= $item[0] ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="px-3 py-3 border-t border-zinc-800 space-y-1">
                <a href="<?= APP_URL ?>" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100 transition-colors">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Voltar ao site
                </a>
                <a href="<?= APP_URL ?>/admin/logout" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sair
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col ml-64">
            <header class="sticky top-0 z-30 h-16 bg-zinc-950/80 backdrop-blur-sm border-b border-zinc-800 flex items-center justify-between px-6">
                <h1 class="text-lg font-semibold text-zinc-100"><?= h($title) ?></h1>
                <div class="flex items-center gap-4">
                    <?php if ($heldCount > 0): ?>
                    <a href="<?= APP_URL ?>/admin/transactions?status=held" class="relative p-2 text-zinc-400 hover:text-amber-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center w-4 h-4 bg-amber-500 text-zinc-950 text-[10px] font-bold rounded-full"><?= $heldCount ?></span>
                    </a>
                    <?php endif; ?>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-amber-500/20 rounded-full flex items-center justify-center">
                            <span class="text-sm font-semibold text-amber-400"><?= strtoupper(substr($adminName, 0, 1)) ?></span>
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-medium text-zinc-100"><?= $adminName ?></p>
                            <p class="text-xs text-zinc-500"><?= $adminRole ?></p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <?= $content ?>
            </main>
        </div>
    </div>
</body>
</html>
