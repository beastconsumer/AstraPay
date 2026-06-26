<?php

require_once __DIR__ . '/../../../backend/config.php';
require_once __DIR__ . '/../../../backend/db.php';
require_once __DIR__ . '/../../../backend/middleware.php';
require_once __DIR__ . '/../../../backend/admin_auth.php';

DB::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    if (empty($input)) {
        $input = $_POST;
    }
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Usuario e senha sao obrigatorios';
    } else {
        $result = adminLogin($username, $password);
        if ($result['success']) {
            header('Location: ' . APP_URL . '/admin');
            exit;
        } else {
            $error = $result['message'] ?? 'Credenciais invalidas';
        }
    }
}

if (isAdmin()) {
    header('Location: ' . APP_URL . '/admin');
    exit;
}

$pdo = DB::connect();
$hasAdmin = $pdo->query("SELECT COUNT(*) as c FROM admin_users")->fetch()['c'] > 0;
?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstraPay Admin - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
    <style>
        body { background-color: #09090b; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-500/10 border border-amber-500/20 rounded-xl mb-4">
                <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h1 class="text-xl font-semibold text-zinc-100">AstraPay Admin</h1>
            <p class="text-sm text-zinc-400 mt-1">Painel de administracao</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
            <?php if (!$hasAdmin): ?>
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-md p-3 mb-4">
                    <p class="text-sm text-amber-400 font-medium">Nenhum admin encontrado</p>
                    <p class="text-xs text-amber-500/80 mt-1">Execute o script de setup para criar o primeiro administrador.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 rounded-md p-3 mb-4">
                    <p class="text-sm text-red-400"><?= h($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/admin/login" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-zinc-300">Usuario</label>
                    <input type="text" name="username" required autofocus
                        class="w-full bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20 transition-colors"
                        placeholder="admin">
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-zinc-300">Senha</label>
                    <input type="password" name="password" required
                        class="w-full bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2.5 text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20 transition-colors"
                        placeholder="Sua senha">
                </div>
                <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm px-4 py-2.5 rounded-md transition-colors">
                    Entrar
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-zinc-600 mt-6">AstraPay Admin Panel - Acesso Restrito</p>
    </div>
</body>
</html>
