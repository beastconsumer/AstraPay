<?php
$pdo = DB::connect();
$stmt = $pdo->query("SELECT * FROM settings ORDER BY key ASC");
$rows = $stmt->fetchAll();
$settings = [];
foreach ($rows as $r) { $settings[$r['key']] = $r; }

$configurableSettings = [
    ['key' => 'default_admin_fee_pct', 'label' => 'Taxa Admin Padrao (%)', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'min_withdrawal_amount', 'label' => 'Valor Minimo de Saque (R$)', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'max_withdrawal_amount', 'label' => 'Valor Maximo de Saque (R$)', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'withdrawal_cooldown_minutes', 'label' => 'Intervalo Minimo entre Saques (min)', 'type' => 'number', 'step' => '1'],
    ['key' => 'fraud_max_accounts_per_ip', 'label' => 'Max Contas por IP', 'type' => 'number', 'step' => '1'],
    ['key' => 'fraud_auto_hold_amount', 'label' => 'Auto-hold acima de (R$)', 'type' => 'number', 'step' => '0.01'],
];

$tierLimitsSettings = [
    ['key' => 'tier_new_daily', 'label' => 'Tier NEW - Limite Diario', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_new_monthly', 'label' => 'Tier NEW - Limite Mensal', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_new_per_tx', 'label' => 'Tier NEW - Limite por TX', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_basic_daily', 'label' => 'Tier BASIC - Limite Diario', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_basic_monthly', 'label' => 'Tier BASIC - Limite Mensal', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_basic_per_tx', 'label' => 'Tier BASIC - Limite por TX', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_bronze_daily', 'label' => 'Tier BRONZE - Limite Diario', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_bronze_monthly', 'label' => 'Tier BRONZE - Limite Mensal', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_bronze_per_tx', 'label' => 'Tier BRONZE - Limite por TX', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_silver_daily', 'label' => 'Tier SILVER - Limite Diario', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_silver_monthly', 'label' => 'Tier SILVER - Limite Mensal', 'type' => 'number', 'step' => '0.01'],
    ['key' => 'tier_silver_per_tx', 'label' => 'Tier SILVER - Limite por TX', 'type' => 'number', 'step' => '0.01'],
];

$saved = $_GET['saved'] ?? false;
?>

<div x-data="settingsManager()" class="space-y-6">
    <?php if ($saved): ?>
    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-md p-4 text-sm text-emerald-400">Configuracoes salvas com sucesso.</div>
    <?php endif; ?>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
        <h3 class="text-sm font-medium text-zinc-100 mb-4">Configuracoes Gerais</h3>
        <div class="space-y-4">
            <?php foreach ($configurableSettings as $cfg): ?>
            <div class="flex items-center gap-4">
                <label class="w-64 text-sm text-zinc-400"><?= h($cfg['label']) ?></label>
                <input type="<?= $cfg['type'] ?>" step="<?= $cfg['step'] ?>"
                    x-model="form['<?= $cfg['key'] ?>']"
                    class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50 transition-colors">
            </div>
            <?php endforeach; ?>

            <div class="flex items-center gap-4">
                <label class="w-64 text-sm text-zinc-400">Modo Manutencao</label>
                <select x-model="form['maintenance_mode']"
                    class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50">
                    <option value="0">Normal</option>
                    <option value="1">Manutencao</option>
                </select>
            </div>

            <div class="flex items-center gap-4">
                <label class="w-64 text-sm text-zinc-400">Nome da Plataforma</label>
                <input type="text" x-model="form['platform_name']"
                    class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50 transition-colors">
            </div>

            <div class="pt-4 border-t border-zinc-800">
                <button @click="saveSettings()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">
                    Salvar Configuracoes Gerais
                </button>
            </div>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
        <h3 class="text-sm font-medium text-zinc-100 mb-4">Limites por Tier</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($tierLimitsSettings as $cfg): ?>
            <div class="space-y-1.5">
                <label class="text-xs text-zinc-500"><?= h($cfg['label']) ?></label>
                <input type="<?= $cfg['type'] ?>" step="<?= $cfg['step'] ?>"
                    x-model="form['<?= $cfg['key'] ?>']"
                    class="w-full bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-amber-500/50 transition-colors">
            </div>
            <?php endforeach; ?>
        </div>
        <div class="pt-4 mt-4 border-t border-zinc-800">
            <button @click="saveTierLimits()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">
                Salvar Limites de Tier
            </button>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-6">
        <h3 class="text-sm font-medium text-zinc-100 mb-4">API Asaas</h3>
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <label class="w-64 text-sm text-zinc-400">Chave API Asaas</label>
                <input type="password" x-model="form['asaas_api_key']"
                    class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 font-mono focus:outline-none focus:border-amber-500/50 transition-colors">
            </div>
            <div class="flex items-center gap-4">
                <label class="w-64 text-sm text-zinc-400">URL API Asaas</label>
                <input type="text" x-model="form['asaas_api_url']"
                    class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 font-mono focus:outline-none focus:border-amber-500/50 transition-colors">
            </div>
            <div class="flex items-center gap-4">
                <label class="w-64 text-sm text-zinc-400">Webhook Secret</label>
                <input type="password" x-model="form['asaas_webhook_secret']"
                    class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-sm text-zinc-100 font-mono focus:outline-none focus:border-amber-500/50 transition-colors">
            </div>
            <div class="pt-4 border-t border-zinc-800">
                <button @click="saveAsaas()" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-medium text-sm rounded-md transition-colors">
                    Salvar Config Asaas
                </button>
            </div>
        </div>
    </div>

    <div x-show="toast.show" x-cloak x-transition class="fixed top-4 right-4 z-50 bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-4 py-3 shadow-lg max-w-sm">
        <p class="text-sm text-emerald-400" x-text="toast.message"></p>
    </div>
</div>

<script>
function settingsManager() {
    return {
        form: {
            <?php
            $all = array_merge($configurableSettings, $tierLimitsSettings, [
                ['key' => 'maintenance_mode'], ['key' => 'platform_name'],
                ['key' => 'asaas_api_key'], ['key' => 'asaas_api_url'], ['key' => 'asaas_webhook_secret'],
            ]);
            $vals = [];
            foreach ($all as $s) {
                $k = $s['key'];
                $v = $settings[$k]['value'] ?? '';
                $vals[] = "'{$k}': " . json_encode($v);
            }
            echo implode(",\n", $vals);
            ?>
        },
        toast: { show: false, message: '' },

        showToast(msg) {
            this.toast.message = msg;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 3000);
        },

        async saveSettings() {
            await this.saveKeys([
                'default_admin_fee_pct', 'min_withdrawal_amount', 'max_withdrawal_amount',
                'withdrawal_cooldown_minutes', 'fraud_max_accounts_per_ip', 'fraud_auto_hold_amount',
                'maintenance_mode', 'platform_name'
            ]);
            this.showToast('Configuracoes gerais salvas');
        },

        async saveTierLimits() {
            await this.saveKeys([
                'tier_new_daily', 'tier_new_monthly', 'tier_new_per_tx',
                'tier_basic_daily', 'tier_basic_monthly', 'tier_basic_per_tx',
                'tier_bronze_daily', 'tier_bronze_monthly', 'tier_bronze_per_tx',
                'tier_silver_daily', 'tier_silver_monthly', 'tier_silver_per_tx',
            ]);
            this.showToast('Limites de tier salvos');
        },

        async saveAsaas() {
            await this.saveKeys(['asaas_api_key', 'asaas_api_url', 'asaas_webhook_secret']);
            this.showToast('Configuracoes Asaas salvas');
        },

        async saveKeys(keys) {
            for (const key of keys) {
                try {
                    await fetch('<?= APP_URL ?>/api/admin/config', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ key: key, value: this.form[key] })
                    });
                } catch(e) {}
            }
            window.location = '<?= APP_URL ?>/admin/settings?saved=1';
        }
    }
}
</script>
