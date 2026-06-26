<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'AstraPay'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'system-ui', 'sans-serif'],
              mono: ['JetBrains Mono', 'monospace'],
            },
            colors: {
              zinc: { 950: '#09090b' },
            }
          }
        }
      }
    </script>

    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-zinc-950 text-zinc-100 font-sans antialiased" data-page="<?php echo $page; ?>">

<?php if ($layout === 'app'): ?>
    <!-- Mobile overlay -->
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="app-sidebar" class="fixed left-0 top-0 h-full w-64 bg-zinc-900 border-r border-zinc-800 z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 custom-scrollbar overflow-y-auto">
        <div class="px-6 py-5 border-b border-zinc-800">
            <a href="/dashboard" class="text-xl font-bold">
                <span class="gradient-text">AstraPay</span>
            </a>
        </div>

        <nav class="px-3 py-4 space-y-1">
            <a href="/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-fast <?php echo $page === 'dashboard' ? 'bg-zinc-800 text-white font-medium' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100'; ?>">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="/pix" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-fast <?php echo $page === 'pix' ? 'bg-zinc-800 text-white font-medium' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100'; ?>">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                Gerar PIX
            </a>
            <a href="/transactions" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-fast <?php echo $page === 'transactions' ? 'bg-zinc-800 text-white font-medium' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100'; ?>">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Transacoes
            </a>
            <a href="/settings" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-fast <?php echo $page === 'settings' ? 'bg-zinc-800 text-white font-medium' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100'; ?>">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Configuracoes
            </a>
        </nav>

        <div class="px-6 py-4 border-t border-zinc-800 absolute bottom-0 left-0 right-0 bg-zinc-900">
            <button onclick="AstraPay.auth.logout()" class="flex items-center gap-3 w-full px-3 py-2 rounded-md text-sm text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100 transition-fast">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Sair
            </button>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:pl-64">
        <!-- Top Header Bar -->
        <header class="sticky top-0 z-20 bg-zinc-950/80 glass-nav border-b border-zinc-800">
            <div class="flex items-center justify-between px-6 py-3">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition-fast">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <?php
                    $titles = [
                        'dashboard' => 'Dashboard',
                        'pix' => 'Gerar PIX',
                        'transactions' => 'Transacoes',
                        'settings' => 'Configuracoes',
                    ];
                    echo '<h1 class="text-lg font-semibold text-zinc-100">' . ($titles[$page] ?? 'AstraPay') . '</h1>';
                    ?>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 rounded-md text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition-fast">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </button>

                    <div class="relative">
                        <button id="user-dropdown-btn" onclick="toggleUserDropdown()" class="flex items-center gap-2 p-1 rounded-lg hover:bg-zinc-800 transition-fast">
                            <div id="dash-user-avatar" class="w-8 h-8 rounded-full bg-violet-500/20 text-violet-400 flex items-center justify-center text-sm font-medium">U</div>
                            <span id="dash-user-name" class="text-sm text-zinc-300 hidden sm:block">Usuario</span>
                            <svg class="w-4 h-4 text-zinc-500 hidden sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="user-dropdown" class="hidden absolute right-0 top-full mt-2 w-56 bg-zinc-900 border border-zinc-800 rounded-lg shadow-lg z-50 py-1">
                            <div class="px-4 py-3 border-b border-zinc-800">
                                <p id="dash-user-email" class="text-sm text-zinc-100 font-medium truncate">email@exemplo.com</p>
                                <span id="dash-user-tier" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1 bg-zinc-800 text-zinc-400 border border-zinc-700">Novo</span>
                            </div>
                            <a href="/settings" class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100 transition-fast">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Configuracoes
                            </a>
                            <button onclick="AstraPay.auth.logout()" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100 transition-fast">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Sair
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
        </main>
    </div>

<?php else: ?>
    <!-- Public/Auth Layout -->
    <main class="min-h-screen">
        <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
    </main>
<?php endif; ?>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none" style="width: auto;">
</div>

<!-- Scripts -->
<script src="/assets/js/app.js"></script>
</body>
</html>
