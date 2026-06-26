<!-- ================================================================
     DASHBOARD PAGE (Auth Required)
     ================================================================ -->

<!-- Unverified Email Banner -->
<div id="dash-verify-banner" class="hidden bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6 flex items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <p class="text-sm text-amber-300">Verifique seu email para aumentar seus limites e habilitar saques.</p>
    </div>
    <button onclick="resendVerification()" class="shrink-0 text-sm text-amber-400 hover:text-amber-300 font-medium transition-fast">Reenviar email</button>
</div>

<!-- Loading State -->
<div id="dash-loading">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
        <div class="skeleton skeleton-card"></div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                <div class="skeleton skeleton-title mb-4"></div>
                <div class="skeleton" style="height: 200px;"></div>
            </div>
        </div>
        <div>
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                <div class="skeleton skeleton-title mb-4"></div>
                <div class="skeleton skeleton-row"></div>
                <div class="skeleton skeleton-row"></div>
                <div class="skeleton skeleton-row"></div>
                <div class="skeleton skeleton-row"></div>
                <div class="skeleton skeleton-row"></div>
            </div>
        </div>
    </div>
</div>

<!-- Content State -->
<div id="dash-content" class="hidden">

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 stagger-children">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 card-lift">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Saldo Disponivel</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <p id="stat-balance" class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums">R$ 0,00</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 card-lift">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Total Recebido</p>
                <div class="w-8 h-8 rounded-lg bg-violet-500/10 border border-violet-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
            </div>
            <p id="stat-received" class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums">R$ 0,00</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 card-lift">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-zinc-400 uppercase tracking-wider">PIX Gerados</p>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
            </div>
            <p id="stat-pix-count" class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums">0</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 card-lift">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Taxa Admin</p>
                <div class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center">
                    <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </div>
            <p id="stat-fee" class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums">0%</p>
        </div>
    </div>

    <!-- Chart + Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart -->
        <div id="dash-chart-container" class="lg:col-span-2">
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                <h3 class="text-sm font-medium text-zinc-400 mb-4">Volume Diario (7 dias)</h3>
                <div class="chart-container" style="height: 240px;">
                    <canvas id="dash-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div>
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-800 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-zinc-400">Transacoes Recentes</h3>
                    <a href="/transactions" class="text-xs text-violet-400 hover:text-violet-300 transition-fast">Ver todas</a>
                </div>
                <table id="dash-tx-table" class="w-full">
                    <tbody></tbody>
                </table>
                <div id="dash-tx-table-empty" class="hidden p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-zinc-800 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    </div>
                    <p class="text-sm text-zinc-500">Nenhuma transacao ainda</p>
                    <a href="/pix" class="inline-block mt-2 text-sm text-violet-400 hover:text-violet-300 transition-fast">Gerar primeiro PIX</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="mt-6">
        <a href="/pix" class="inline-flex items-center gap-2 px-5 py-3 rounded-lg text-sm font-medium bg-violet-500 hover:bg-violet-400 text-white transition-fast shadow-lg shadow-violet-500/25">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Gerar PIX
        </a>
    </div>

</div>
