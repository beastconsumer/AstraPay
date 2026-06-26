<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas API Keys - AstraPay</title>
    <meta name="description" content="Gerencie suas chaves de API AstraPay">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
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
        body { background: #09090b; }
        .toast { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 font-sans antialiased min-h-screen">

<div x-data="apiKeysManager()" x-init="fetchKeys()" class="min-h-screen flex flex-col">

    <nav class="border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-6 h-14 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 text-white font-bold text-lg">
                <svg class="w-7 h-7 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                AstraPay
            </a>
            <div class="flex items-center gap-3 text-sm">
                <a href="/api-docs" class="text-zinc-400 hover:text-white transition-colors">API Docs</a>
                <a href="/dashboard" class="text-zinc-400 hover:text-white transition-colors">Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-4xl mx-auto px-6 py-12 w-full">
        <div class="mb-10">
            <h1 class="text-3xl font-bold mb-2">Minhas API Keys</h1>
            <p class="text-zinc-400">Gerencie suas chaves de API para integrar PIX nos seus projetos.</p>
        </div>

        <div class="mb-8 flex items-center gap-4">
            <button @click="showGenerateModal = true"
                    class="px-5 py-2.5 bg-violet-500 hover:bg-violet-400 text-white rounded-lg font-medium transition-all duration-150 active:scale-95 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova API Key
            </button>
            <a href="/api-docs" class="px-5 py-2.5 border border-zinc-700 hover:border-violet-500/50 text-zinc-300 hover:text-white rounded-lg font-medium transition-all duration-150 text-sm">
                Ver Documentacao
            </a>
        </div>

        <div x-show="loading" class="space-y-4">
            <template x-for="i in 3" :key="i">
                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 animate-pulse">
                    <div class="h-5 bg-zinc-800 rounded w-48 mb-4"></div>
                    <div class="h-4 bg-zinc-800 rounded w-80 mb-2"></div>
                    <div class="h-3 bg-zinc-800 rounded w-40"></div>
                </div>
            </template>
        </div>

        <div x-show="!loading && keys.length === 0" class="bg-zinc-900 border border-zinc-800 rounded-xl p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">Nenhuma API Key</h3>
            <p class="text-zinc-500 text-sm mb-6">Voce ainda nao possui chaves de API. Crie uma para comecar a integrar.</p>
            <button @click="showGenerateModal = true"
                    class="px-5 py-2.5 bg-violet-500 hover:bg-violet-400 text-white rounded-lg font-medium transition-all duration-150">
                Criar Primeira Key
            </button>
        </div>

        <div x-show="!loading && keys.length > 0" class="space-y-4">
            <template x-for="key in keys" :key="key.id">
                <div class="bg-zinc-900 border border-zinc-800 hover:border-zinc-700 rounded-xl p-6 transition-all duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-semibold text-white" x-text="key.name"></h3>
                                <span x-show="key.is_active" class="px-2 py-0.5 text-xs rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Ativa</span>
                                <span x-show="!key.is_active" class="px-2 py-0.5 text-xs rounded-full bg-red-500/10 text-red-400 border border-red-500/20">Revogada</span>
                            </div>
                            <div class="flex items-center gap-4 text-sm">
                                <code class="font-mono text-zinc-500 bg-zinc-950 px-2 py-0.5 rounded" x-text="key.api_key_masked"></code>
                            </div>
                            <div class="flex items-center gap-4 mt-2 text-xs text-zinc-500">
                                <span x-text="'Criada: ' + new Date(key.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('pt-BR')"></span>
                                <span x-show="key.last_used_at" x-text="'Ultimo uso: ' + new Date(key.last_used_at.replace(' ', 'T') + 'Z').toLocaleDateString('pt-BR')"></span>
                                <span x-show="!key.last_used_at">Nunca usada</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button @click="rotateKey(key.id)" x-show="key.is_active"
                                    class="px-3 py-1.5 border border-zinc-700 hover:border-violet-500/50 text-zinc-400 hover:text-violet-400 rounded-md text-xs font-medium transition-all duration-150"
                                    :disabled="rotating === key.id">
                                <span x-show="rotating !== key.id">Rotacionar</span>
                                <span x-show="rotating === key.id" class="flex items-center gap-1">
                                    <svg class="animate-spin w-3 h-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Rotacionando
                                </span>
                            </button>
                            <button @click="revokeKey(key.id)" x-show="key.is_active"
                                    class="px-3 py-1.5 border border-red-500/20 hover:bg-red-500/10 text-red-400 rounded-md text-xs font-medium transition-all duration-150"
                                    :disabled="revoking === key.id">
                                Revogar
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="!loading && keys.length > 0" class="mt-8 p-4 bg-zinc-900 border border-zinc-800 rounded-xl">
            <p class="text-sm text-zinc-500">
                <span class="text-zinc-400 font-medium">Limite:</span> 10 chaves por conta.
                Rate limit padrao: 60 requisicoes/minuto por chave. As chaves revogadas deixam de funcionar imediatamente.
            </p>
        </div>
    </main>

    <div x-show="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showGenerateModal = false; generatedKey = null"></div>
        <div class="relative bg-zinc-900 border border-zinc-800 rounded-xl shadow-2xl max-w-md w-full p-6" @click.outside="showGenerateModal = false; generatedKey = null">
            <template x-if="!generatedKey">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-1">Nova API Key</h3>
                    <p class="text-sm text-zinc-400 mb-5">De um nome para identificar esta chave.</p>
                    <label class="block text-sm font-medium text-zinc-300 mb-1.5">Nome</label>
                    <input x-model="newKeyName" type="text" placeholder="Ex: Meu App, Loja Virtual..."
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 focus:outline-none focus:border-violet-500/50 focus:ring-1 focus:ring-violet-500/20 transition-colors text-sm">
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button @click="showGenerateModal = false"
                                class="px-4 py-2 text-zinc-400 hover:text-white text-sm rounded-lg transition-colors">Cancelar</button>
                        <button @click="generateKey()"
                                class="px-5 py-2 bg-violet-500 hover:bg-violet-400 text-white rounded-lg font-medium text-sm transition-all duration-150 disabled:opacity-50"
                                :disabled="generating">
                            <span x-show="!generating">Gerar Chave</span>
                            <span x-show="generating" class="flex items-center gap-1">
                                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Gerando...
                            </span>
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="generatedKey">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-white">Chave Gerada!</h3>
                            <p class="text-xs text-zinc-400" x-text="newKeyName"></p>
                        </div>
                    </div>

                    <div class="bg-amber-500/5 border border-amber-500/20 rounded-lg p-3 mb-4">
                        <p class="text-amber-400 text-xs font-medium">Atencao: Esta chave sera exibida apenas uma vez. Copie e armazene em local seguro.</p>
                    </div>

                    <div class="bg-zinc-950 border border-zinc-800 rounded-lg p-3 mb-4 relative group">
                        <code class="font-mono text-sm text-violet-400 break-all pr-8" x-text="generatedKey"></code>
                        <button @click="copyKey(generatedKey); showCopyToast = true; setTimeout(() => showCopyToast = false, 2000)"
                                class="absolute top-2 right-2 p-1.5 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-400 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                    </div>

                    <div x-show="showCopyToast" class="text-emerald-400 text-xs mb-3 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Copiado para a area de transferencia!
                    </div>

                    <button @click="showGenerateModal = false; generatedKey = null; newKeyName = ''; fetchKeys()"
                            class="w-full px-5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg font-medium text-sm transition-all duration-150">
                        Entendi, Fechar
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div x-show="showRotateModal && rotatedKey" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showRotateModal = false; rotatedKey = null"></div>
        <div class="relative bg-zinc-900 border border-zinc-800 rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-10 h-10 rounded-full bg-violet-500/10 border border-violet-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Chave Rotacionada</h3>
                    <p class="text-xs text-zinc-400">A chave antiga foi desativada.</p>
                </div>
            </div>

            <div class="bg-amber-500/5 border border-amber-500/20 rounded-lg p-3 mb-4">
                <p class="text-amber-400 text-xs font-medium">Atencao: Guarde esta nova chave. A anterior nao funciona mais.</p>
            </div>

            <div class="bg-zinc-950 border border-zinc-800 rounded-lg p-3 mb-4 relative group">
                <code class="font-mono text-sm text-violet-400 break-all pr-8" x-text="rotatedKey"></code>
                <button @click="copyKey(rotatedKey); showRotateCopyToast = true; setTimeout(() => showRotateCopyToast = false, 2000)"
                        class="absolute top-2 right-2 p-1.5 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </div>
            <div x-show="showRotateCopyToast" class="text-emerald-400 text-xs mb-3 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Copiado!
            </div>

            <button @click="showRotateModal = false; rotatedKey = null; fetchKeys()"
                    class="w-full px-5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg font-medium text-sm transition-all duration-150">
                Fechar
            </button>
        </div>
    </div>

    <div x-show="showToast" class="fixed top-4 right-4 z-100 toast" x-transition>
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 shadow-xl max-w-sm flex items-start gap-3"
             :class="toastType === 'success' ? 'border-emerald-500/30' : 'border-red-500/30'">
            <div x-show="toastType === 'success'" class="w-8 h-8 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div x-show="toastType === 'error'" class="w-8 h-8 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-white" x-text="toastTitle"></p>
                <p class="text-xs text-zinc-400 mt-0.5" x-text="toastMessage"></p>
            </div>
            <button @click="showToast = false" class="text-zinc-500 hover:text-zinc-300 ml-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function apiKeysManager() {
        return {
            keys: [],
            loading: true,
            showGenerateModal: false,
            showRotateModal: false,
            newKeyName: '',
            generatedKey: null,
            rotatedKey: null,
            rotating: null,
            revoking: null,
            generating: false,
            showToast: false,
            toastType: 'success',
            toastTitle: '',
            toastMessage: '',
            showCopyToast: false,
            showRotateCopyToast: false,

            async fetchKeys() {
                this.loading = true;
                try {
                    const resp = await fetch('/api/keys/list');
                    const data = await resp.json();
                    if (data.success) this.keys = data.data.keys;
                } catch (e) {
                    this.showNotification('Erro', 'Falha ao carregar API keys.', 'error');
                }
                this.loading = false;
            },

            async generateKey() {
                this.generating = true;
                try {
                    const resp = await fetch('/api/keys/generate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name: this.newKeyName || 'Default' }),
                    });
                    const data = await resp.json();
                    if (data.success) {
                        this.generatedKey = data.data.key.api_key;
                        this.showNotification('Sucesso', 'API Key gerada com sucesso!', 'success');
                    } else {
                        this.showNotification('Erro', data.error || 'Falha ao gerar key.', 'error');
                        this.showGenerateModal = false;
                    }
                } catch (e) {
                    this.showNotification('Erro', 'Falha ao gerar API key.', 'error');
                    this.showGenerateModal = false;
                }
                this.generating = false;
            },

            async revokeKey(id) {
                if (!confirm('Tem certeza que deseja revogar esta chave? Ela deixara de funcionar imediatamente.')) return;
                this.revoking = id;
                try {
                    const resp = await fetch('/api/keys/' + id + '/revoke', { method: 'POST' });
                    const data = await resp.json();
                    if (data.success) {
                        this.showNotification('Revogada', 'API Key revogada com sucesso.', 'success');
                        this.fetchKeys();
                    } else {
                        this.showNotification('Erro', data.error || 'Falha ao revogar.', 'error');
                    }
                } catch (e) {
                    this.showNotification('Erro', 'Falha ao revogar API key.', 'error');
                }
                this.revoking = null;
            },

            async rotateKey(id) {
                if (!confirm('Rotacionar gera uma nova chave e desativa a atual. Continuar?')) return;
                this.rotating = id;
                try {
                    const resp = await fetch('/api/keys/' + id + '/rotate', { method: 'POST' });
                    const data = await resp.json();
                    if (data.success) {
                        this.rotatedKey = data.data.key.api_key;
                        this.showRotateModal = true;
                    } else {
                        this.showNotification('Erro', data.error || 'Falha ao rotacionar.', 'error');
                    }
                } catch (e) {
                    this.showNotification('Erro', 'Falha ao rotacionar API key.', 'error');
                }
                this.rotating = null;
            },

            async copyKey(key) {
                try {
                    await navigator.clipboard.writeText(key);
                } catch (e) {
                    const ta = document.createElement('textarea');
                    ta.value = key;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }
            },

            showNotification(title, message, type = 'success') {
                this.toastTitle = title;
                this.toastMessage = message;
                this.toastType = type;
                this.showToast = true;
                setTimeout(() => this.showToast = false, 4000);
            },
        };
    }
</script>

</body>
</html>
