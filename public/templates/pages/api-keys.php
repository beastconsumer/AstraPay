<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div id="api-keys-app">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.25rem;font-weight:600;color:#ffffff;">Minhas API Keys</h1>
            <p style="font-size:0.8125rem;color:#888;margin-top:2px;">Gerencie suas chaves de API para integrar PIX nos seus projetos.</p>
        </div>
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <button id="btn-new-api-key" class="btn-primary" style="text-decoration:none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nova API Key
            </button>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
        <a href="/api-docs" class="btn-secondary" style="text-decoration:none;font-size:0.75rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            Ver Documentacao
        </a>
    </div>

    <div id="keys-loading" style="display:none;flex-direction:column;gap:0.75rem;">
        <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;height:72px;animation:pulse 1.5s infinite;"></div>
        <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;height:72px;animation:pulse 1.5s infinite;"></div>
    </div>

    <div id="keys-empty" style="display:none;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:2.5rem;text-align:center;">
        <div style="width:56px;height:56px;border-radius:12px;background:#0a0a0a;border:1px solid #1a1a1a;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
        </div>
        <h3 style="font-size:1rem;font-weight:500;color:#fff;margin-bottom:0.375rem;">Nenhuma API Key</h3>
        <p style="font-size:0.8125rem;color:#888;margin-bottom:1rem;">Voce ainda nao possui chaves de API. Crie uma para comecar a integrar.</p>
        <button id="btn-create-first-key" class="btn-primary">Criar Primeira Key</button>
    </div>

    <div id="keys-list-container" style="display:none;">
        <div id="keys-list" style="display:flex;flex-direction:column;gap:0.75rem;"></div>
    </div>

    <div id="keys-limit-footer" style="margin-top:1rem;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:0.875rem 1rem;">
        <p style="font-size:0.75rem;color:#666;">
            <span style="color:#888;font-weight:500;">Limite:</span> 10 chaves por conta. Rate limit padrao: 60 requisicoes/minuto. Chaves revogadas param de funcionar imediatamente.
        </p>
    </div>

    <!-- Generate Modal -->
    <div id="generate-modal" style="display:none;position:fixed;inset:0;z-index:100;align-items:center;justify-content:center;padding:1rem;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);" onclick="closeGenerateModal()"></div>
        <div style="position:relative;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:12px;max-width:400px;width:100%;padding:1.5rem;" @click.outside="showGenerateModal = false; generatedKey = null">
            <div id="modal-form-view">
                <div>
                    <h3 style="font-size:1rem;font-weight:600;color:#fff;margin-bottom:0.25rem;">Nova API Key</h3>
                    <p style="font-size:0.75rem;color:#888;margin-bottom:1rem;">De um nome para identificar esta chave.</p>
                    <label style="display:block;font-size:0.75rem;font-weight:500;color:#888;margin-bottom:0.375rem;">Nome</label>
                    <input id="modal-key-name" type="text" placeholder="Ex: Meu App, Loja Virtual..."
                           style="width:100%;padding:0.5rem 0.75rem;background:#000;border:1px solid #1a1a1a;border-radius:6px;color:#fff;font-size:0.8125rem;outline:none;font-family:'Inter',system-ui,sans-serif;box-sizing:border-box;"
                           @keydown.enter="generateKey()">
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.5rem;margin-top:1rem;">
                        <button onclick="closeGenerateModalClean()" class="btn-secondary" style="font-size:0.75rem;">Cancelar</button>
                        <button onclick="generateKeyClick()" class="btn-primary" style="font-size:0.75rem;" :disabled="generating">
                            <span id="modal-generate-text">Gerar Chave</span>
                            <span id="modal-generate-spinner" style="display:none;align-items:center;gap:0.25rem;"><span class="spinner" style="width:12px;height:12px;border-width:2px;"></span> Gerando...</span> Gerando...</span>
                        </button>
                    </div>
                </div>
            </div>
            <div id="modal-result-view" style="display:none;">
                <div>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                        <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.05);border:1px solid #333;display:flex;align-items:center;justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:0.875rem;font-weight:500;color:#fff;">Chave Gerada</h3>
                            <p id="result-key-name" style="font-size:0.6875rem;color:#888;"></p>
                        </div>
                    </div>

                    <div style="background:rgba(255,255,0,0.05);border:1px solid rgba(255,255,0,0.1);border-radius:6px;padding:0.625rem 0.75rem;margin-bottom:0.75rem;">
                        <p style="font-size:0.6875rem;color:#888;font-weight:500;">Atencao: Esta chave sera exibida apenas uma vez. Copie e armazene em local seguro.</p>
                    </div>

                    <div style="background:#000;border:1px solid #1a1a1a;border-radius:6px;padding:0.625rem 0.75rem;margin-bottom:0.75rem;position:relative;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                        <code id="result-key-code" style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#fff;word-break:break-all;"></code>
                        <button onclick="copyGeneratedKey()"
                                style="flex-shrink:0;padding:0.25rem 0.5rem;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:4px;color:#888;cursor:pointer;font-size:0.6875rem;font-family:'Inter',system-ui,sans-serif;">
                            Copiar
                        </button>
                    </div>

                    <p id="copy-toast-gen" style="display:none;font-size:0.6875rem;color:#fff;margin-bottom:0.75rem;align-items:center;gap:0.25rem;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        Copiado
                    </p>

                    <button onclick="closeGenerateModalAndRefresh()"
                            class="btn-primary" style="width:100%;font-size:0.75rem;">Entendi, Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rotate Result Modal -->
    <div id="rotate-modal" style="display:none;position:fixed;inset:0;z-index:100;align-items:center;justify-content:center;padding:1rem;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);" @click="showRotateModal = false; rotatedKey = null"></div>
        <div style="position:relative;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:12px;max-width:400px;width:100%;padding:1.5rem;">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.05);border:1px solid #333;display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
                <div>
                    <h3 style="font-size:0.875rem;font-weight:500;color:#fff;">Chave Rotacionada</h3>
                    <p style="font-size:0.6875rem;color:#888;">A chave antiga foi desativada.</p>
                </div>
            </div>

            <div style="background:rgba(255,255,0,0.05);border:1px solid rgba(255,255,0,0.1);border-radius:6px;padding:0.625rem 0.75rem;margin-bottom:0.75rem;">
                <p style="font-size:0.6875rem;color:#888;font-weight:500;">Atencao: Guarde esta nova chave. A anterior nao funciona mais.</p>
            </div>

            <div style="background:#000;border:1px solid #1a1a1a;border-radius:6px;padding:0.625rem 0.75rem;margin-bottom:0.75rem;position:relative;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                <code id="rotate-key-code" style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#fff;word-break:break-all;flex:1;"></code>
                <button onclick="copyRotatedKey()"
                        style="flex-shrink:0;padding:0.25rem 0.5rem;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:4px;color:#888;cursor:pointer;font-size:0.6875rem;font-family:'Inter',system-ui,sans-serif;">
                    Copiar
                </button>
            </div>
            <p id="copy-toast-rotate" style="display:none;font-size:0.6875rem;color:#fff;margin-bottom:0.75rem;align-items:center;gap:0.25rem;">Copiado</p>

            <button @click="showRotateModal = false; rotatedKey = null; fetchKeys()" class="btn-primary" style="width:100%;font-size:0.75rem;">Fechar</button>
        </div>
    </div>

    <!-- Toast -->
    <div id="api-toast" style="display:none;position:fixed;top:1rem;right:1rem;z-index:200;pointer-events:auto;">
        <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:0.75rem 1rem;display:flex;align-items:flex-start;gap:0.75rem;max-width:320px;box-shadow:0 8px 32px rgba(0,0,0,0.5);"
             :class="toastType === 'error' ? 'border-red-500/30' : ''">
            <div class="toast-icon-box" style="flex-shrink:0;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.05);border:1px solid #333;color:#fff;"
                 :class="toastType === 'error' ? 'background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;' : 'background:rgba(255,255,255,0.05);border:1px solid #333;color:#fff;'">
                <svg class="toast-icon-success" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                <svg class="toast-icon-error" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <p class="toast-title" style="font-size:0.8125rem;font-weight:500;color:#fff;"></p>
                <p class="toast-msg" style="font-size:0.6875rem;color:#888;margin-top:2px;"></p>
            </div>
            <button onclick="document.getElementById('api-toast').style.display='none'" style="flex-shrink:0;background:none;border:none;color:#666;cursor:pointer;padding:0;font-size:0.875rem;">&times;</button>
        </div>
    </div>
</div>

<script>
// ============================================================
// VANILLA JS FALLBACK - Works without Alpine.js
// ============================================================
(function() {
    'use strict';

    var state = {
        keys: [],
        loading: true,
        newKeyName: '',
        generatedKey: null,
        rotatedKey: null,
        generating: false,
        rotating: null,
        revoking: null
    };

    // DOM refs
    function $(s) { return document.querySelector(s); }
    function $$(s) { return document.querySelectorAll(s); }

    function getToken() {
        return localStorage.getItem('astrapay_token') || '';
    }

    function showToast(title, msg, type) {
        type = type || 'success';
        var el = document.getElementById('api-toast');
        if (!el) return;
        el.querySelector('.toast-title').textContent = title;
        el.querySelector('.toast-msg').textContent = msg;
        el.style.display = 'block';
        if (type === 'error') {
            el.querySelector('.toast-icon-box').style.background = 'rgba(239,68,68,0.1)';
            el.querySelector('.toast-icon-box').style.border = '1px solid rgba(239,68,68,0.2)';
        } else {
            el.querySelector('.toast-icon-box').style.background = 'rgba(255,255,255,0.05)';
            el.querySelector('.toast-icon-box').style.border = '1px solid #333';
        }
        clearTimeout(el._timeout);
        el._timeout = setTimeout(function() { el.style.display = 'none'; }, 4000);
    }

    async function fetchKeys() {
        var token = getToken();
        if (!token) {
            window.location.href = '/login';
            return;
        }
        state.loading = true;
        render();
        try {
            var resp = await fetch('/api/keys/list', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                }
            });
            if (resp.status === 401) {
                localStorage.removeItem('astrapay_token');
                localStorage.removeItem('astrapay_user');
                window.location.href = '/login';
                return;
            }
            var data = await resp.json();
            if (data.success) {
                state.keys = data.data.keys || [];
            }
        } catch(e) {
            showToast('Erro', 'Falha ao carregar API keys.', 'error');
        }
        state.loading = false;
        render();
    }

    async function generateKey() {
        state.generating = true;
        renderModal();
        try {
            var resp = await fetch('/api/keys/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: state.newKeyName || 'Default' })
            });
            var data = await resp.json();
            if (data.success) {
                state.generatedKey = data.data.key.api_key;
                showToast('Sucesso', 'API Key gerada com sucesso', 'success');
            } else {
                showToast('Erro', data.error || 'Falha ao gerar key.', 'error');
                state.generatedKey = null;
                document.getElementById('generate-modal').style.display = 'none';
            }
        } catch(e) {
            showToast('Erro', 'Falha ao gerar API key.', 'error');
            state.generatedKey = null;
            document.getElementById('generate-modal').style.display = 'none';
        }
        state.generating = false;
        renderModal();
    }

    async function revokeKey(id) {
        if (!confirm('Tem certeza que deseja revogar esta chave? Ela deixara de funcionar imediatamente.')) return;
        state.revoking = id;
        render();
        try {
            var resp = await fetch('/api/keys/revoke/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken(),
                    'Accept': 'application/json'
                }
            });
            var data = await resp.json();
            if (data.success) {
                showToast('Revogada', 'API Key revogada com sucesso.', 'success');
                await fetchKeys();
            } else {
                showToast('Erro', data.error || 'Falha ao revogar.', 'error');
            }
        } catch(e) {
            showToast('Erro', 'Falha ao revogar API key.', 'error');
        }
        state.revoking = null;
        render();
    }

    async function rotateKey(id) {
        if (!confirm('Rotacionar gera uma nova chave e desativa a atual. Continuar?')) return;
        state.rotating = id;
        render();
        try {
            var resp = await fetch('/api/keys/rotate/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken(),
                    'Accept': 'application/json'
                }
            });
            var data = await resp.json();
            if (data.success) {
                state.rotatedKey = data.data.key.api_key;
                document.getElementById('rotate-modal').style.display = 'flex';
            } else {
                showToast('Erro', data.error || 'Falha ao rotacionar.', 'error');
            }
        } catch(e) {
            showToast('Erro', 'Falha ao rotacionar API key.', 'error');
        }
        state.rotating = null;
        render();
    }

    // ============================================================
    // RENDER FUNCTIONS
    // ============================================================
    function render() {
        // Loading state
        var loadingEl = document.getElementById('keys-loading');
        var emptyEl = document.getElementById('keys-empty');
        var listEl = document.getElementById('keys-list');
        var listContainer = document.getElementById('keys-list-container');

        if (state.loading) {
            if (loadingEl) loadingEl.style.display = 'flex';
            if (emptyEl) emptyEl.style.display = 'none';
            if (listContainer) listContainer.style.display = 'none';
            return;
        }
        if (loadingEl) loadingEl.style.display = 'none';

        if (state.keys.length === 0) {
            if (emptyEl) emptyEl.style.display = 'block';

            var footer = document.getElementById('keys-limit-footer'); if (footer) footer.style.display = 'none';            if (listContainer) listContainer.style.display = 'none';
        } else {
            if (emptyEl) emptyEl.style.display = 'none';
            if (listContainer) listContainer.style.display = 'block';

            var footer = document.getElementById('keys-limit-footer');
            if (footer) footer.style.display = 'block';
            if (listEl) {
                var h = '';
                state.keys.forEach(function(key) {
                    var created = key.created_at ? new Date(key.created_at.replace(' ', 'T') + 'Z').toLocaleDateString('pt-BR') : '-';
                    var lastUsed = key.last_used_at ? new Date(key.last_used_at.replace(' ', 'T') + 'Z').toLocaleDateString('pt-BR') : null;
                    var isRotating = state.rotating === key.id;
                    var isRevoking = state.revoking === key.id;
                    h += '<div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;">';
                    h += '<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">';
                    h += '<div style="flex:1;min-width:0;">';
                    h += '<div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">';
                    h += '<h3 style="font-size:0.875rem;font-weight:500;color:#fff;">' + escHtml(key.name || '') + '</h3>';
                    if (key.is_active) {
                        h += '<span style="display:inline-block;padding:1px 6px;border-radius:9999px;font-size:0.625rem;color:#fff;border:1px solid #333;">Ativa</span>';
                    } else {
                        h += '<span style="display:inline-block;padding:1px 6px;border-radius:9999px;font-size:0.625rem;color:#ef4444;border:1px solid rgba(239,68,68,0.2);">Revogada</span>';
                    }
                    h += '</div>';
                    h += '<code style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#666;background:#000;padding:2px 6px;border-radius:4px;">' + escHtml(key.api_key_masked || '') + '</code>';
                    h += '<div style="display:flex;align-items:center;gap:1rem;margin-top:0.5rem;font-size:0.6875rem;color:#666;">';
                    h += '<span>Criada: ' + created + '</span>';
                    if (lastUsed) {
                        h += '<span>Ultimo uso: ' + lastUsed + '</span>';
                    } else {
                        h += '<span>Nunca usada</span>';
                    }
                    h += '</div></div>';
                    h += '<div style="display:flex;align-items:center;gap:0.5rem;">';
                    if (key.is_active) {
                        h += '<button onclick="rotateKeyClick(this)" data-key-id="' + key.id + '" class="btn-secondary" style="font-size:0.6875rem;padding:0.375rem 0.625rem;"' + (isRotating ? ' disabled' : '') + '>';
                        if (isRotating) {
                            h += '<span style="display:flex;align-items:center;gap:0.25rem;"><span class="spinner" style="width:12px;height:12px;border-width:2px;"></span></span>';
                        } else {
                            h += 'Rotacionar';
                        }
                        h += '</button>';
                        h += '<button onclick="revokeKeyClick(this)" data-key-id="' + key.id + '" class="btn-danger" style="font-size:0.6875rem;padding:0.375rem 0.625rem;"' + (isRevoking ? ' disabled' : '') + '>Revogar</button>';
                    }
                    h += '</div></div></div>';
                });
                listEl.innerHTML = h;
            }
        }
    }

    function escHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderModal() {
        var formView = document.getElementById('modal-form-view');
        var resultView = document.getElementById('modal-result-view');
        var genBtn = document.getElementById('modal-generate-btn');
        var genSpinner = document.getElementById('modal-generate-spinner');
        if (state.generatedKey) {
            if (formView) formView.style.display = 'none';
            if (resultView) resultView.style.display = 'block';
            var keyName = document.getElementById('result-key-name');
            if (keyName) keyName.textContent = state.newKeyName || 'Default';
            var keyCode = document.getElementById('result-key-code');
            if (keyCode) keyCode.textContent = state.generatedKey;
        } else {
            if (formView) formView.style.display = 'block';
            if (resultView) resultView.style.display = 'none';
        }
        if (genBtn) genBtn.disabled = state.generating;
        if (genSpinner) genSpinner.style.display = state.generating ? 'flex' : 'none';
        var genText = document.getElementById('modal-generate-text');
        if (genText) genText.style.display = state.generating ? 'none' : 'inline';
    }

    // ============================================================
    // EVENT HANDLERS
    // ============================================================
    window.openGenerateModal = function() {
        state.newKeyName = '';
        state.generatedKey = null;
        state.generating = false;
        var inp = document.getElementById('modal-key-name');
        if (inp) inp.value = '';
        renderModal();
        document.getElementById('generate-modal').style.display = 'flex';
    };

    window.closeGenerateModal = function() {
        document.getElementById('generate-modal').style.display = 'none';
    };

    window.closeGenerateModalClean = function() {
        state.generatedKey = null;
        state.newKeyName = '';
        state.generating = false;
        document.getElementById('generate-modal').style.display = 'none';
    };

    window.closeGenerateModalAndRefresh = function() {
        state.generatedKey = null;
        state.newKeyName = '';
        state.generating = false;
        document.getElementById('generate-modal').style.display = 'none';
        fetchKeys();
    };

    window.generateKeyClick = function() {
        var inp = document.getElementById('modal-key-name');
        state.newKeyName = (inp && inp.value) ? inp.value.trim() : '';
        generateKey();
    };

    window.rotateKeyClick = function(el) {
        var id = parseInt(el.getAttribute('data-key-id'));
        if (id) rotateKey(id);
    };

    window.revokeKeyClick = function(el) {
        var id = parseInt(el.getAttribute('data-key-id'));
        if (id) revokeKey(id);
    };

    window.copyGeneratedKey = function() {
        var code = document.getElementById('result-key-code');
        if (!code) return;
        copyToClipboard(code.textContent);
        var toast = document.getElementById('copy-toast-gen');
        if (toast) {
            toast.style.display = 'flex';
            setTimeout(function() { toast.style.display = 'none'; }, 2000);
        }
    };

    window.copyRotatedKey = function() {
        var code = document.getElementById('rotate-key-code');
        if (!code) return;
        copyToClipboard(code.textContent);
        var toast = document.getElementById('copy-toast-rotate');
        if (toast) {
            toast.style.display = 'flex';
            setTimeout(function() { toast.style.display = 'none'; }, 2000);
        }
    };

    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).catch(function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }

    // Enter key in modal input
    var modalInput = document.getElementById('modal-key-name');
    if (modalInput) {
        modalInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') generateKeyClick();
        });
    }

    // Init
    fetchKeys();
})();
</script>

<style>
[x-cloak] { display: none !important; }
</style>
