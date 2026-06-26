<?php
$pdo = DB::connect();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$tierFilter = trim($_GET['tier'] ?? '');
$bannedFilter = $_GET['banned'] ?? '';

$where = [];
$params = [];
if (!empty($search)) { $where[] = "(name LIKE ? OR email LIKE ? OR cpf LIKE ?)"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
if (!empty($tierFilter) && in_array($tierFilter, ['new','basic','bronze','silver','gold'])) { $where[] = "tier = ?"; $params[] = $tierFilter; }
if ($bannedFilter !== '' && in_array($bannedFilter, ['0','1'])) { $where[] = "banned = ?"; $params[] = (int)$bannedFilter; }

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare("SELECT COUNT(*) as c FROM users {$whereClause}");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $limit));

$stmt = $pdo->prepare("SELECT * FROM users {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?");
$allParams = array_merge($params, [$limit, $offset]);
$stmt->execute($allParams);
$users = $stmt->fetchAll();
?>

<div x-data="userManager()" class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" action="<?= APP_URL ?>/admin/users" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="<?= h($search) ?>" placeholder="Buscar por nome, email ou CPF..."
                    class="w-full bg-zinc-900 border border-zinc-800 rounded-md pl-10 pr-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/20 transition-colors">
            </div>
            <select name="tier" class="bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                <option value="">Todos os tiers</option>
                <option value="new" <?= $tierFilter === 'new' ? 'selected' : '' ?>>New</option>
                <option value="basic" <?= $tierFilter === 'basic' ? 'selected' : '' ?>>Basic</option>
                <option value="bronze" <?= $tierFilter === 'bronze' ? 'selected' : '' ?>>Bronze</option>
                <option value="silver" <?= $tierFilter === 'silver' ? 'selected' : '' ?>>Silver</option>
                <option value="gold" <?= $tierFilter === 'gold' ? 'selected' : '' ?>>Gold</option>
            </select>
            <select name="banned" class="bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                <option value="">Status</option>
                <option value="0" <?= $bannedFilter === '0' ? 'selected' : '' ?>>Ativo</option>
                <option value="1" <?= $bannedFilter === '1' ? 'selected' : '' ?>>Banido</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">Filtrar</button>
            <?php if (!empty($search) || !empty($tierFilter) || $bannedFilter !== ''): ?>
            <a href="<?= APP_URL ?>/admin/users" class="px-3 py-2 text-sm text-zinc-400 hover:text-zinc-200 transition-colors">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-900/50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">CPF</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Tier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Saldo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wider">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="px-4 py-12 text-center text-zinc-500">Nenhum usuario encontrado</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <button @click="openUser(<?= $u['id'] ?>)" class="font-medium text-zinc-100 hover:text-amber-400 transition-colors text-left">
                                    <?= h($u['name']) ?>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-zinc-400"><?= h($u['email']) ?></td>
                            <td class="px-4 py-3 text-zinc-500 font-mono text-xs"><?= formatCPF($u['cpf']) ?></td>
                            <td class="px-4 py-3">
                                <?php
                                $tierColors = ['new' => 'zinc', 'basic' => 'blue', 'bronze' => 'amber', 'silver' => 'slate', 'gold' => 'yellow'];
                                $tc = $tierColors[$u['tier']] ?? 'zinc';
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $tc ?>-500/10 text-<?= $tc ?>-400 border border-<?= $tc ?>-500/20"><?= h($u['tier']) ?></span>
                            </td>
                            <td class="px-4 py-3 font-mono text-zinc-100 tabular-nums text-xs"><?= formatBRL($u['current_balance']) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($u['banned']): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">Banido</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Ativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="openUser(<?= $u['id'] ?>)" class="text-xs px-2 py-1 rounded bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-zinc-200 transition-colors mr-1">Detalhes</button>
                                <?php if ($u['banned']): ?>
                                    <button @click="unbanUser(<?= $u['id'] ?>)" class="text-xs px-2 py-1 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">Desbanir</button>
                                <?php else: ?>
                                    <button @click="showBanModal(<?= $u['id'] ?>, '<?= h(addslashes($u['name'])) ?>')" class="text-xs px-2 py-1 rounded bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors">Banir</button>
                                <?php endif; ?>
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
                    <?php
                    $q = http_build_query(array_filter(['page' => $i, 'search' => $search, 'tier' => $tierFilter, 'banned' => $bannedFilter]));
                    ?>
                    <a href="?<?= $q ?>" class="px-3 py-1 text-xs rounded <?= $i === $page ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800' ?> transition-colors"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div x-show="userPanelOpen" x-cloak class="fixed inset-0 z-50 flex" @keydown.escape.window="closeUser()">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeUser()"></div>
        <div class="relative ml-auto w-full max-w-lg bg-zinc-900 border-l border-zinc-800 h-full overflow-y-auto shadow-2xl" x-show="userPanelOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="sticky top-0 bg-zinc-900 border-b border-zinc-800 px-6 py-4 flex items-center justify-between z-10">
                <h2 class="text-lg font-semibold text-zinc-100" x-text="userDetail?.name || 'Carregando...'"></h2>
                <button @click="closeUser()" class="p-1.5 text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800 rounded-md transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <template x-if="loading">
                    <div class="space-y-4">
                        <div class="animate-pulse bg-zinc-800 rounded h-6 w-3/4"></div>
                        <div class="animate-pulse bg-zinc-800 rounded h-4 w-1/2"></div>
                        <div class="animate-pulse bg-zinc-800 rounded h-20 w-full"></div>
                    </div>
                </template>
                <template x-if="!loading && userDetail">
                    <div>
                        <div class="bg-zinc-950 border border-zinc-800 rounded-lg p-4 space-y-3 mb-4">
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Email</span>
                                <span class="text-sm text-zinc-200" x-text="userDetail.email"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">CPF</span>
                                <span class="text-sm text-zinc-200 font-mono" x-text="formatCPF(userDetail.cpf)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Telefone</span>
                                <span class="text-sm text-zinc-200" x-text="userDetail.phone || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Tier</span>
                                <span class="text-sm text-zinc-200" x-text="userDetail.tier"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Email Verificado</span>
                                <span class="text-sm" :class="userDetail.email_verified ? 'text-emerald-400' : 'text-red-400'" x-text="userDetail.email_verified ? 'Sim' : 'Nao'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Saldo</span>
                                <span class="text-sm font-mono text-zinc-100" x-text="'R$ ' + Number(userDetail.current_balance).toLocaleString('pt-BR', {minimumFractionDigits:2})"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Total Recebido</span>
                                <span class="text-sm font-mono text-zinc-100" x-text="'R$ ' + Number(userDetail.total_received).toLocaleString('pt-BR', {minimumFractionDigits:2})"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">IP Registro</span>
                                <span class="text-sm text-zinc-400 font-mono text-xs" x-text="userDetail.ip_registered || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Ultimo Login</span>
                                <span class="text-sm text-zinc-400" x-text="userDetail.last_login_at ? new Date(userDetail.last_login_at).toLocaleString('pt-BR') : '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500">Banido</span>
                                <span class="text-sm" :class="userDetail.banned ? 'text-red-400' : 'text-emerald-400'" x-text="userDetail.banned ? 'Sim - ' + (userDetail.ban_reason||'') : 'Nao'"></span>
                            </div>
                        </div>

                        <div class="bg-zinc-950 border border-zinc-800 rounded-lg p-4 mb-4">
                            <h4 class="text-sm font-medium text-zinc-100 mb-3">Alterar Tier</h4>
                            <div class="flex gap-2">
                                <select x-model="newTier" class="flex-1 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                                    <option value="new">new</option>
                                    <option value="basic">basic</option>
                                    <option value="bronze">bronze</option>
                                    <option value="silver">silver</option>
                                    <option value="gold">gold</option>
                                </select>
                                <button @click="changeTier()" class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">Aplicar</button>
                            </div>
                        </div>

                        <div class="bg-zinc-950 border border-zinc-800 rounded-lg p-4 mb-4">
                            <h4 class="text-sm font-medium text-zinc-100 mb-3">Limites Personalizados</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-zinc-500">Limite Diario (R$)</label>
                                    <input type="number" step="0.01" x-model="limits.daily_limit" class="w-full mt-1 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                                </div>
                                <div>
                                    <label class="text-xs text-zinc-500">Limite Mensal (R$)</label>
                                    <input type="number" step="0.01" x-model="limits.monthly_limit" class="w-full mt-1 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                                </div>
                                <div>
                                    <label class="text-xs text-zinc-500">Limite por TX (R$)</label>
                                    <input type="number" step="0.01" x-model="limits.per_tx_limit" class="w-full mt-1 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                                </div>
                                <div>
                                    <label class="text-xs text-zinc-500">Taxa Admin (%)</label>
                                    <input type="number" step="0.01" x-model="limits.admin_fee_pct" class="w-full mt-1 bg-zinc-900 border border-zinc-800 rounded-md px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                                </div>
                                <button @click="updateLimits()" class="w-full px-3 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">Salvar Limites</button>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <template x-if="!userDetail.banned">
                                <button @click="showBanModal(userDetail.id, userDetail.name)" class="flex-1 px-3 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 font-medium text-sm rounded-md transition-colors">Banir Usuario</button>
                            </template>
                            <template x-if="userDetail.banned">
                                <button @click="unbanUser(userDetail.id)" class="flex-1 px-3 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 font-medium text-sm rounded-md transition-colors">Desbanir Usuario</button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-show="banModalOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center" @keydown.escape.window="banModalOpen = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="banModalOpen = false"></div>
        <div class="relative bg-zinc-900 border border-zinc-800 rounded-lg shadow-2xl max-w-md w-full mx-4 p-6" x-show="banModalOpen" x-transition>
            <h3 class="text-lg font-semibold text-zinc-100 mb-2">Banir Usuario</h3>
            <p class="text-sm text-zinc-400 mb-4" x-text="'Confirmar banimento de ' + banTargetName + '?'"></p>
            <div class="space-y-1.5 mb-4">
                <label class="text-xs font-medium text-zinc-400">Motivo</label>
                <input type="text" x-model="banReason" class="w-full bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-red-500/50 transition-colors" placeholder="Motivo do banimento">
            </div>
            <div class="flex justify-end gap-3">
                <button @click="banModalOpen = false" class="px-4 py-2 text-sm text-zinc-400 hover:text-zinc-200 transition-colors">Cancelar</button>
                <button @click="confirmBan()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium text-sm rounded-md transition-colors">Confirmar Banimento</button>
            </div>
        </div>
    </div>
</div>

<script>
function formatCPF(cpf) {
    if (!cpf) return '-';
    const d = cpf.replace(/\D/g, '');
    if (d.length !== 11) return cpf;
    return d.slice(0,3)+'.'+d.slice(3,6)+'.'+d.slice(6,9)+'-'+d.slice(9);
}

function userManager() {
    return {
        userPanelOpen: false,
        userDetail: null,
        loading: false,
        newTier: 'basic',
        limits: { daily_limit: 0, monthly_limit: 0, per_tx_limit: 0, admin_fee_pct: 0 },
        banModalOpen: false,
        banTargetId: null,
        banTargetName: '',
        banReason: '',
        selectedUserId: null,

        async openUser(id) {
            this.selectedUserId = id;
            this.userPanelOpen = true;
            this.loading = true;
            try {
                const res = await fetch('<?= APP_URL ?>/api/admin/users/' + id);
                const data = await res.json();
                if (data.success) {
                    this.userDetail = data.data.user;
                    this.newTier = data.data.user.tier;
                    this.limits = {
                        daily_limit: data.data.user.daily_limit,
                        monthly_limit: data.data.user.monthly_limit,
                        per_tx_limit: data.data.user.per_tx_limit,
                        admin_fee_pct: data.data.user.admin_fee_pct,
                    };
                }
            } catch(e) {} finally { this.loading = false; }
        },

        closeUser() {
            this.userPanelOpen = false;
            this.userDetail = null;
            this.selectedUserId = null;
        },

        async changeTier() {
            if (!this.selectedUserId) return;
            try {
                const res = await fetch('<?= APP_URL ?>/api/admin/users/' + this.selectedUserId + '/tier', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ tier: this.newTier })
                });
                const data = await res.json();
                if (data.success) {
                    this.userDetail.tier = this.newTier;
                    setTimeout(() => location.reload(), 300);
                }
            } catch(e) {}
        },

        async updateLimits() {
            if (!this.selectedUserId) return;
            try {
                const res = await fetch('<?= APP_URL ?>/api/admin/users/' + this.selectedUserId + '/limits', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.limits)
                });
                const data = await res.json();
                if (data.success) location.reload();
            } catch(e) {}
        },

        showBanModal(id, name) {
            this.banTargetId = id;
            this.banTargetName = name;
            this.banReason = 'Violacao dos termos de uso';
            this.banModalOpen = true;
        },

        async confirmBan() {
            if (!this.banTargetId) return;
            try {
                const res = await fetch('<?= APP_URL ?>/api/admin/users/' + this.banTargetId + '/ban', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason: this.banReason || 'Banido pelo admin' })
                });
                const data = await res.json();
                if (data.success) {
                    this.banModalOpen = false;
                    location.reload();
                }
            } catch(e) {}
        },

        async unbanUser(id) {
            if (!confirm('Confirmar desbanimento deste usuario?')) return;
            try {
                const res = await fetch('<?= APP_URL ?>/api/admin/users/' + id + '/unban', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (data.success) location.reload();
            } catch(e) {}
        },
    }
}
</script>
