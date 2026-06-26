<?php
$pdo = DB::connect();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 30;
$offset = ($page - 1) * $limit;
$actionFilter = trim($_GET['action'] ?? '');
$userFilter = trim($_GET['user_id'] ?? '');

$where = [];
$params = [];
if (!empty($actionFilter)) { $where[] = "a.action LIKE ?"; $params[] = "%{$actionFilter}%"; }
if (!empty($userFilter)) { $where[] = "(a.user_id = ? OR a.entity_id = ?)"; $params[] = (int)$userFilter; $params[] = (int)$userFilter; }

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) as c FROM audit_log a {$whereClause}");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $limit));

$stmt = $pdo->prepare("
    SELECT a.*, u.name as user_name, ad.username as admin_username
    FROM audit_log a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN admin_users ad ON a.admin_id = ad.id
    {$whereClause}
    ORDER BY a.created_at DESC
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$limit, $offset]);
$stmt->execute($allParams);
$logs = $stmt->fetchAll();
?>

<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" action="<?= APP_URL ?>/admin/audit" class="flex flex-wrap items-center gap-3 flex-1">
            <select name="action" class="bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                <option value="">Todas as acoes</option>
                <option value="user.banned" <?= $actionFilter === 'user.banned' ? 'selected' : '' ?>>user.banned</option>
                <option value="user.unbanned" <?= $actionFilter === 'user.unbanned' ? 'selected' : '' ?>>user.unbanned</option>
                <option value="user.tier_changed" <?= $actionFilter === 'user.tier_changed' ? 'selected' : '' ?>>user.tier_changed</option>
                <option value="user.limits_changed" <?= $actionFilter === 'user.limits_changed' ? 'selected' : '' ?>>user.limits_changed</option>
                <option value="settings.changed" <?= $actionFilter === 'settings.changed' ? 'selected' : '' ?>>settings.changed</option>
                <option value="admin.login" <?= $actionFilter === 'admin.login' ? 'selected' : '' ?>>admin.login</option>
                <option value="tx.released" <?= $actionFilter === 'tx.released' ? 'selected' : '' ?>>tx.released</option>
                <option value="tx.cancelled" <?= $actionFilter === 'tx.cancelled' ? 'selected' : '' ?>>tx.cancelled</option>
            </select>
            <input type="text" name="user_id" value="<?= h($userFilter) ?>" placeholder="User ID..."
                class="w-32 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:border-amber-500/50 transition-colors">
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">Filtrar</button>
            <?php if (!empty($actionFilter) || !empty($userFilter)): ?>
            <a href="<?= APP_URL ?>/admin/audit" class="px-3 py-2 text-sm text-zinc-400 hover:text-zinc-200 transition-colors">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-900/50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Data/Hora</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Admin</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Acao</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Entidade</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">IP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="7" class="px-4 py-12 text-center text-zinc-500">Nenhum registro de auditoria encontrado</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                        <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 py-3 text-zinc-400 text-xs whitespace-nowrap"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                            <td class="px-4 py-3 text-zinc-300"><?= h($log['admin_username'] ?? 'Sistema') ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-800 text-zinc-300 border border-zinc-700"><?= h($log['action']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-zinc-400">
                                <?php if ($log['user_id']): ?>
                                <a href="<?= APP_URL ?>/admin/users?search=<?= $log['user_id'] ?>" class="text-amber-400 hover:text-amber-300 transition-colors"><?= h($log['user_name'] ?? 'User #' . $log['user_id']) ?></a>
                                <?php else: ?>
                                <span class="text-zinc-600">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-zinc-500 text-xs">
                                <?= h($log['entity_type'] ?? '-') ?>
                                <?php if ($log['entity_id']): ?>#<?= $log['entity_id'] ?><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 font-mono text-xs"><?= h($log['ip_address'] ?? '-') ?></td>
                            <td class="px-4 py-3">
                                <button onclick="showDetails(this)" data-old='<?= h($log['old_values'] ?? '') ?>' data-new='<?= h($log['new_values'] ?? '') ?>' data-meta='<?= h($log['metadata'] ?? '') ?>'
                                    class="text-xs text-amber-400 hover:text-amber-300 transition-colors">
                                    <?php if ($log['old_values'] || $log['new_values']): ?>Ver diff<?php else: ?>-<?php endif; ?>
                                </button>
                            </td>
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
                    <?php $q = http_build_query(array_filter(['page' => $i, 'action' => $actionFilter, 'user_id' => $userFilter])); ?>
                    <a href="?<?= $q ?>" class="px-3 py-1 text-xs rounded <?= $i === $page ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800' ?> transition-colors"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div x-data="auditDetail()" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="open = false">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
    <div class="relative bg-zinc-900 border border-zinc-800 rounded-lg shadow-2xl max-w-lg w-full mx-4 max-h-[80vh] overflow-y-auto p-6" x-show="open" x-transition>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-zinc-100">Detalhes da Auditoria</h3>
            <button @click="open = false" class="p-1 text-zinc-400 hover:text-zinc-200 rounded"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="space-y-4">
            <div>
                <h4 class="text-xs font-medium text-zinc-400 uppercase tracking-wider mb-2">Valores Anteriores</h4>
                <pre class="bg-zinc-950 border border-zinc-800 rounded-md p-3 text-xs text-zinc-300 font-mono whitespace-pre-wrap overflow-x-auto" x-text="oldVal || '(nenhum)'"></pre>
            </div>
            <div>
                <h4 class="text-xs font-medium text-zinc-400 uppercase tracking-wider mb-2">Novos Valores</h4>
                <pre class="bg-zinc-950 border border-zinc-800 rounded-md p-3 text-xs text-zinc-300 font-mono whitespace-pre-wrap overflow-x-auto" x-text="newVal || '(nenhum)'"></pre>
            </div>
            <template x-if="metaVal">
                <div>
                    <h4 class="text-xs font-medium text-zinc-400 uppercase tracking-wider mb-2">Metadados</h4>
                    <pre class="bg-zinc-950 border border-zinc-800 rounded-md p-3 text-xs text-zinc-300 font-mono whitespace-pre-wrap overflow-x-auto" x-text="metaVal"></pre>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function auditDetail() {
    return {
        open: false,
        oldVal: '',
        newVal: '',
        metaVal: '',
    }
}
function showDetails(btn) {
    const detail = document.querySelector('[x-data="auditDetail()"]');
    const component = detail.__x || Alpine.$data(detail);
    if (component) {
        try {
            component.oldVal = btn.dataset.old ? JSON.stringify(JSON.parse(btn.dataset.old), null, 2) : '';
        } catch(e) { component.oldVal = btn.dataset.old; }
        try {
            component.newVal = btn.dataset.new ? JSON.stringify(JSON.parse(btn.dataset.new), null, 2) : '';
        } catch(e) { component.newVal = btn.dataset.new; }
        try {
            component.metaVal = btn.dataset.meta ? JSON.stringify(JSON.parse(btn.dataset.meta), null, 2) : '';
        } catch(e) { component.metaVal = btn.dataset.meta; }
        component.open = true;
    }
}
</script>
