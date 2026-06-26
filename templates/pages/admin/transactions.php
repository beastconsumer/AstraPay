<?php
$pdo = DB::connect();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;
$statusFilter = trim($_GET['status'] ?? '');
$userIdFilter = trim($_GET['user_id'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$where = [];
$params = [];
$validStatuses = ['pending', 'confirmed', 'received', 'refunded', 'cancelled', 'held'];
if (!empty($statusFilter) && in_array($statusFilter, $validStatuses)) { $where[] = "t.status = ?"; $params[] = $statusFilter; }
if (!empty($userIdFilter)) { $where[] = "t.user_id = ?"; $params[] = (int)$userIdFilter; }
if (!empty($dateFrom)) { $where[] = "t.created_at >= ?"; $params[] = $dateFrom . ' 00:00:00'; }
if (!empty($dateTo)) { $where[] = "t.created_at <= ?"; $params[] = $dateTo . ' 23:59:59'; }

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) as c FROM transactions t {$whereClause}");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $limit));

$stmt = $pdo->prepare("
    SELECT t.*, u.name as user_name, u.email as user_email
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    {$whereClause}
    ORDER BY t.created_at DESC
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$limit, $offset]);
$stmt->execute($allParams);
$transactions = $stmt->fetchAll();
?>

<div x-data="txManager()" class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" action="<?= APP_URL ?>/admin/transactions" class="flex flex-wrap items-center gap-3 flex-1">
            <select name="status" class="bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                <option value="">Todos os status</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pendente</option>
                <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmado</option>
                <option value="received" <?= $statusFilter === 'received' ? 'selected' : '' ?>>Recebido</option>
                <option value="held" <?= $statusFilter === 'held' ? 'selected' : '' ?>>Em Revisao</option>
                <option value="refunded" <?= $statusFilter === 'refunded' ? 'selected' : '' ?>>Reembolsado</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
            </select>
            <input type="text" name="user_id" value="<?= h($userIdFilter) ?>" placeholder="User ID..."
                class="w-28 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:border-amber-500/50 transition-colors">
            <input type="date" name="date_from" value="<?= h($dateFrom) ?>"
                class="bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50 transition-colors">
            <input type="date" name="date_to" value="<?= h($dateTo) ?>"
                class="bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50 transition-colors">
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">Filtrar</button>
            <?php if (!empty($statusFilter) || !empty($userIdFilter) || !empty($dateFrom) || !empty($dateTo)): ?>
            <a href="<?= APP_URL ?>/admin/transactions" class="px-3 py-2 text-sm text-zinc-400 hover:text-zinc-200 transition-colors">Limpar</a>
            <?php endif; ?>
        </form>
        <span x-data="{ts: new Date().toLocaleTimeString('pt-BR')}" x-init="setInterval(() => { location.reload() }, 30000)" class="text-xs text-zinc-600 ml-auto">Auto-refresh 30s</span>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-900/50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Valor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Taxa</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Liquido</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Asaas ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="8" class="px-4 py-12 text-center text-zinc-500">Nenhuma transacao encontrada</td></tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                        <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/50 transition-colors cursor-pointer" @click="openTx(<?= $tx['id'] ?>)">
                            <td class="px-4 py-3 font-mono text-xs text-zinc-500">#<?= $tx['id'] ?></td>
                            <td class="px-4 py-3 text-zinc-400"><?= h($tx['user_name'] ?? $tx['user_email'] ?? 'N/A') ?></td>
                            <td class="px-4 py-3 font-mono text-zinc-100 tabular-nums text-xs"><?= formatBRL($tx['amount']) ?></td>
                            <td class="px-4 py-3 font-mono text-zinc-500 tabular-nums text-xs"><?= formatBRL($tx['fee_amount']) ?></td>
                            <td class="px-4 py-3 font-mono text-zinc-100 tabular-nums text-xs"><?= formatBRL($tx['net_amount']) ?></td>
                            <td class="px-4 py-3">
                                <?php
                                $sColors = [
                                    'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'confirmed' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'received' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'held' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                    'refunded' => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
                                    'cancelled' => 'bg-zinc-500/10 text-zinc-500 border-zinc-500/20',
                                ];
                                $sc = $sColors[$tx['status']] ?? 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20';
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border <?= $sc ?>"><?= h($tx['status']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-zinc-500 text-xs"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></td>
                            <td class="px-4 py-3 text-zinc-600 font-mono text-xs"><?= h($tx['asaas_payment_id'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="px-4 py-3 border-t border-zinc-800 flex items-center justify-between">
            <p class="text-xs text-zinc-500">Mostrando <?= $offset + 1 ?>-<?= min($offset + $limit, $total) ?> de <?= $total ?></p>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php $q = http_build_query(array_filter(['page' => $i, 'status' => $statusFilter, 'user_id' => $userIdFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo])); ?>
                    <a href="?<?= $q ?>" class="px-3 py-1 text-xs rounded <?= $i === $page ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800' ?> transition-colors"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div x-show="txModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="txModalOpen = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="txModalOpen = false"></div>
        <div class="relative bg-zinc-900 border border-zinc-800 rounded-lg shadow-2xl max-w-lg w-full mx-4 max-h-[80vh] overflow-y-auto p-6" x-show="txModalOpen" x-transition>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-zinc-100">Transacao #<span x-text="txDetail?.id"></span></h3>
                <button @click="txModalOpen = false" class="p-1 text-zinc-400 hover:text-zinc-200 rounded"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <template x-if="txDetail">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-zinc-500">Valor Bruto</span><span class="font-mono text-zinc-100" x-text="'R$ ' + Number(txDetail.amount).toLocaleString('pt-BR',{minimumFractionDigits:2})"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Taxa</span><span class="font-mono text-zinc-400" x-text="'R$ ' + Number(txDetail.fee_amount).toLocaleString('pt-BR',{minimumFractionDigits:2}) + ' (' + txDetail.fee_percent + '%)'"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Valor Liquido</span><span class="font-mono text-zinc-100" x-text="'R$ ' + Number(txDetail.net_amount).toLocaleString('pt-BR',{minimumFractionDigits:2})"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Status</span><span class="text-zinc-200" x-text="txDetail.status"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Usuario</span><span class="text-zinc-200" x-text="txDetail.user_name || txDetail.user_email || 'N/A'"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Pagador</span><span class="text-zinc-200" x-text="txDetail.payer_name || '-'"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Descricao</span><span class="text-zinc-200" x-text="txDetail.description || '-'"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Asaas ID</span><span class="font-mono text-xs text-zinc-400" x-text="txDetail.asaas_payment_id || '-'"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">External Ref</span><span class="font-mono text-xs text-zinc-400" x-text="txDetail.external_ref || '-'"></span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Criado em</span><span class="text-zinc-200" x-text="new Date(txDetail.created_at).toLocaleString('pt-BR')"></span></div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function txManager() {
    return {
        txModalOpen: false,
        txDetail: null,
        async openTx(id) {
            try {
                const res = await fetch('<?= APP_URL ?>/api/admin/transactions?user_id=' + id + '&limit=1');
                const data = await res.json();
                if (data.success && data.data.length > 0) {
                    const tx = data.data.find(t => t.id == id);
                    if (tx) { this.txDetail = tx; this.txModalOpen = true; return; }
                }
                this.txDetail = {
                    id: id, amount: 0, fee_amount: 0, fee_percent: 0, net_amount: 0,
                    status: 'unknown', user_name: 'N/A', payer_name: '-', description: '-',
                    asaas_payment_id: '-', external_ref: '-', created_at: new Date().toISOString()
                };
                this.txModalOpen = true;
            } catch(e) {}
        }
    }
}
</script>
