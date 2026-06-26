<?php

$pdo = DB::connect();

$totalUsers = $pdo->query("SELECT COUNT(*) as c FROM users")->fetch()['c'];
$activeToday = $pdo->query("SELECT COUNT(*) as c FROM users WHERE last_login_at >= datetime('now', '-1 day')")->fetch()['c'];
$totalVolume = $pdo->query("SELECT COALESCE(SUM(amount), 0) as s FROM transactions WHERE status IN ('confirmed','received')")->fetch()['s'];
$pendingWithdrawals = $pdo->query("SELECT COUNT(*) as c FROM withdrawals WHERE status IN ('pending','processing')")->fetch()['c'];
$heldCount = $pdo->query("SELECT COUNT(*) as c FROM transactions WHERE held = 1 AND status = 'held'")->fetch()['c'];
$totalFees30d = $pdo->query("SELECT COALESCE(SUM(fee_amount), 0) as s FROM transactions WHERE status IN ('confirmed','received') AND created_at >= datetime('now', '-30 days')")->fetch()['s'];

$dailyVolume = $pdo->query("
    SELECT date(created_at) as dt, COALESCE(SUM(amount),0) as amt, COALESCE(SUM(fee_amount),0) as fee, COUNT(*) as cnt
    FROM transactions WHERE status IN ('confirmed','received') AND created_at >= datetime('now', '-7 days')
    GROUP BY date(created_at) ORDER BY dt ASC
")->fetchAll();

$recentUsers = $pdo->query("SELECT id, name, email, tier, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

$heldTransactions = $pdo->query("
    SELECT t.*, u.name as user_name, u.email as user_email
    FROM transactions t LEFT JOIN users u ON t.user_id = u.id
    WHERE t.held = 1 AND t.status = 'held'
    ORDER BY t.created_at DESC LIMIT 5
")->fetchAll();
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 hover:border-zinc-700 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Usuarios</span>
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <p class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums"><?= number_format($totalUsers, 0, ',', '.') ?></p>
            <p class="text-xs text-zinc-500 mt-1"><?= $activeToday ?> ativos hoje</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 hover:border-zinc-700 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Volume Total</span>
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums"><?= formatBRL($totalVolume) ?></p>
            <p class="text-xs text-zinc-500 mt-1">taxas: <?= formatBRL($totalFees30d) ?> (30d)</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 hover:border-zinc-700 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Saques Pendentes</span>
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums"><?= $pendingWithdrawals ?></p>
            <p class="text-xs text-zinc-500 mt-1">processamento</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 hover:border-zinc-700 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Em Revisao</span>
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <p class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums"><?= $heldCount ?></p>
            <p class="text-xs text-zinc-500 mt-1">transacoes</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-4 hover:border-zinc-700 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Ativos Hoje</span>
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <p class="text-2xl font-mono font-semibold text-zinc-100 tabular-nums"><?= $activeToday ?></p>
            <p class="text-xs text-zinc-500 mt-1">ultimas 24h</p>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
        <h3 class="text-sm font-medium text-zinc-400 mb-4">Volume Diario (7 dias)</h3>
        <div class="h-64">
            <canvas id="volumeChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-zinc-900 border border-zinc-800 rounded-lg">
            <div class="px-5 py-4 border-b border-zinc-800 flex items-center justify-between">
                <h3 class="text-sm font-medium text-zinc-100">Usuarios Recentes</h3>
                <a href="<?= APP_URL ?>/admin/users" class="text-xs text-amber-400 hover:text-amber-300 transition-colors">Ver todos</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Tier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentUsers)): ?>
                            <tr><td colspan="3" class="px-4 py-6 text-center text-zinc-500">Nenhum usuario encontrado</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentUsers as $u): ?>
                            <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/50 transition-colors cursor-pointer" onclick="window.location='<?= APP_URL ?>/admin/users'">
                                <td class="px-4 py-3 font-medium text-zinc-100"><?= h($u['name']) ?></td>
                                <td class="px-4 py-3 text-zinc-400"><?= h($u['email']) ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20"><?= h($u['tier']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-lg">
            <div class="px-5 py-4 border-b border-zinc-800 flex items-center justify-between">
                <h3 class="text-sm font-medium text-zinc-100">Transacoes em Revisao</h3>
                <?php if ($heldCount > 0): ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20"><?= $heldCount ?> pendentes</span>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Motivo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wider">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($heldTransactions)): ?>
                            <tr><td colspan="4" class="px-4 py-6 text-center text-zinc-500">Nenhuma transacao pendente de revisao</td></tr>
                        <?php else: ?>
                            <?php foreach ($heldTransactions as $htx): ?>
                            <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/50 transition-colors" id="held-row-<?= $htx['id'] ?>">
                                <td class="px-4 py-3 font-mono text-zinc-100 tabular-nums"><?= formatBRL($htx['amount']) ?></td>
                                <td class="px-4 py-3 text-zinc-400"><?= h($htx['user_name'] ?? $htx['user_email'] ?? 'N/A') ?></td>
                                <td class="px-4 py-3 text-zinc-500 text-xs max-w-[120px] truncate"><?= h($htx['hold_reason'] ?? 'Revisao manual') ?></td>
                                <td class="px-4 py-3 text-right">
                                    <button onclick="reviewTransaction(<?= $htx['id'] ?>, 'approve')" class="text-xs px-2 py-1 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors mr-1">Aprovar</button>
                                    <button onclick="reviewTransaction(<?= $htx['id'] ?>, 'reject')" class="text-xs px-2 py-1 rounded bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors">Rejeitar</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const volumeData = <?= json_encode($dailyVolume) ?>;
const labels = volumeData.map(d => {
    const parts = d.dt.split('-');
    return parts[2] + '/' + parts[1];
});
const amounts = volumeData.map(d => parseFloat(d.amt));
const fees = volumeData.map(d => parseFloat(d.fee));

const ctx = document.getElementById('volumeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Volume (R$)',
                data: amounts,
                backgroundColor: 'rgba(245, 158, 11, 0.3)',
                borderColor: 'rgba(245, 158, 11, 0.6)',
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Taxas (R$)',
                data: fees,
                backgroundColor: 'rgba(16, 185, 129, 0.25)',
                borderColor: 'rgba(16, 185, 129, 0.5)',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                labels: { color: '#a1a1aa', font: { family: 'Inter', size: 11 }, padding: 16, usePointStyle: true }
            },
        },
        scales: {
            x: {
                grid: { color: '#27272a', drawBorder: false },
                ticks: { color: '#71717a', font: { family: 'Inter', size: 11 } }
            },
            y: {
                grid: { color: '#27272a', drawBorder: false },
                ticks: { color: '#71717a', font: { family: 'Inter', size: 11 }, callback: v => 'R$ ' + v.toLocaleString('pt-BR') }
            }
        }
    }
});

async function reviewTransaction(id, action) {
    try {
        const res = await fetch('<?= APP_URL ?>/api/admin/transactions/' + id + '/review', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: action })
        });
        const data = await res.json();
        if (data.success) {
            const row = document.getElementById('held-row-' + id);
            if (row) row.style.opacity = '0.4';
            setTimeout(() => { if (row) row.remove(); }, 500);
        }
    } catch(e) {}
}
</script>
