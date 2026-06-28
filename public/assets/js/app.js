(function () {
    'use strict';

    var API_BASE = '';

    /* ---- Auth ---- */
    var auth = {
        get token() { return localStorage.getItem('astrapay_token'); },
        set token(v) { v ? localStorage.setItem('astrapay_token', v) : localStorage.removeItem('astrapay_token'); },
        get user() { try { var u = localStorage.getItem('astrapay_user'); return u ? JSON.parse(u) : null; } catch (e) { return null; } },
        set user(v) { v ? localStorage.setItem('astrapay_user', JSON.stringify(v)) : localStorage.removeItem('astrapay_user'); },
        isAuthenticated: function() { return !!auth.token; },
        require: function() { if (!auth.isAuthenticated()) { window.location.href = '/login'; return false; } return true; },
        me: function() { return apiGet('/api/auth/me'); },
        logout: function() { auth.token = null; auth.user = null; window.location.href = '/'; },
        getTierLabel: function(t) { var m = {new:'Novo',basic:'Basico',bronze:'Bronze',silver:'Prata',gold:'Ouro'}; return m[t] || t || 'Novo'; },
        getTierClass: function(t) { return 'badge-tier'; }
    };

    /* ---- API ---- */
    async function apiFetch(path, options) {
        options = options || {};
        var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        if (options.headers) { Object.keys(options.headers).forEach(function(k) { headers[k] = options.headers[k]; }); }
        if (auth.token) { headers['Authorization'] = 'Bearer ' + auth.token; }
        try {
            var resp = await fetch(API_BASE + path, { method: options.method || 'GET', headers: headers, body: options.body || undefined });
            var data = await resp.json().catch(function() { return null; });
            if (resp.status === 401) {
                auth.token = null; auth.user = null;
                window.location.href = '/login';
                throw new Error('Sessao expirada');
            }
            if (!resp.ok) { throw new Error((data && (data.error || data.message)) || 'Erro na requisicao'); }
            return data;
        } catch (e) {
            if (e.name === 'TypeError' && e.message.includes('fetch')) throw new Error('Erro de conexao');
            throw e;
        }
    }

    function apiGet(path) { return apiFetch(path, { method: 'GET' }); }
    function apiPost(path, data) { return apiFetch(path, { method: 'POST', body: JSON.stringify(data) }); }
    function apiPut(path, data) { return apiFetch(path, { method: 'PUT', body: JSON.stringify(data) }); }
    function apiDelete(path) { return apiFetch(path, { method: 'DELETE' }); }

    /* ---- Toast ---- */
    function toast(type, msg, dur) {
        dur = dur || 4000;
        var c = document.getElementById('toast-container');
        if (!c) return;
        var el = document.createElement('div');
        el.className = 'toast-item animate-toast-in';
        el.style.pointerEvents = 'auto';
        var iconColors = { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#ffffff' };
        var icons = { success: '&#x2713;', error: '&#x2715;', warning: '&#x26A0;', info: '&#x2139;' };
        el.innerHTML = '<span style="color:' + (iconColors[type] || '#ffffff') + ';font-size:0.875rem;flex-shrink:0;">' + (icons[type] || '') + '</span><span style="font-size:0.8125rem;color:#cccccc;flex:1;">' + escHtml(msg) + '</span>';
        c.appendChild(el);
        setTimeout(function() { el.classList.remove('animate-toast-in'); el.classList.add('animate-toast-out'); setTimeout(function() { el.remove(); }, 200); }, dur);
    }

    function escHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    /* ---- Format ---- */
    var fmt = {
        brl: function(v) { var n = parseFloat(v) || 0; return 'R$ ' + n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
        date: function(iso) { if (!iso) return '-'; var d = new Date(iso + (iso.includes('T') ? '' : 'T00:00:00')); return isNaN(d.getTime()) ? iso : d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }); },
        dateTime: function(iso) { if (!iso) return '-'; var d = new Date(iso + (iso.includes('T') ? '' : 'T00:00:00')); return isNaN(d.getTime()) ? iso : d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }); },
        int: function(v) { return parseInt(v, 10).toLocaleString('pt-BR'); },
        sid: function(id) { return '#' + String(id).padStart(6, '0'); },
        truncId: function(id) { var s = String(id); return s.length > 8 ? s.substring(0, 8) + '...' : s; }
    };

    /* ---- Mask ---- */
    var mask = {
        cpf: function(inp) { inp.addEventListener('input', function() { var v = this.value.replace(/\D/g, '').substring(0, 11); if (v.length > 9) v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4'); else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{3}).*/, '$1.$2.$3'); else if (v.length > 3) v = v.replace(/^(\d{3})(\d{3}).*/, '$1.$2'); this.value = v; }); },
        phone: function(inp) { inp.addEventListener('input', function() { var v = this.value.replace(/\D/g, '').substring(0, 11); if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3'); else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3'); else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2'); this.value = v; }); },
        currency: function(inp) {
            inp.addEventListener('input', function() {
                var v = this.value.replace(/\D/g, '');
                if (!v) { this.value = ''; return; }
                this.value = (parseFloat(v) / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            });
            inp.addEventListener('blur', function() {
                var n = parseFloat(this.value.replace(/\./g, '').replace(',', '.')) || 0;
                if (n > 0) this.value = n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            });
        },
        rawCur: function(inp) { return parseFloat(inp.value.replace(/\./g, '').replace(',', '.')) || 0; }
    };

    /* ---- DOM helpers ---- */
    function $(s, p) { return (p || document).querySelector(s); }
    function $$(s, p) { return Array.from((p || document).querySelectorAll(s)); }
    function sh(s) { var e = $(s); if (e) e.classList.remove('hidden'); }
    function hi(s) { var e = $(s); if (e) e.classList.add('hidden'); }

    /* ---- Status Badge ---- */
    function statusBadge(st) {
        var map = {
            pending: { l: 'Pendente', c: 'badge-warning' },
            confirmed: { l: 'Confirmado', c: 'badge-success' },
            received: { l: 'Recebido', c: 'badge-success' },
            paid: { l: 'Pago', c: 'badge-success' },
            refunded: { l: 'Estornado', c: 'badge-danger' },
            cancelled: { l: 'Cancelado', c: 'badge-danger' },
            expired: { l: 'Expirado', c: 'badge-danger' },
            held: { l: 'Em Revisao', c: 'badge-warning' },
            processing: { l: 'Processando', c: 'badge-white' },
            completed: { l: 'Concluido', c: 'badge-success' },
            failed: { l: 'Falhou', c: 'badge-danger' }
        };
        var s = map[st] || { l: st, c: 'badge-neutral' };
        return '<span class="badge ' + s.c + '">' + s.l + '</span>';
    }

    function emptyMessages(status) {
        var m = {
            all: 'Nenhuma transacao encontrada',
            pending: 'Nenhum PIX pendente',
            confirmed: 'Nenhum PIX confirmado',
            cancelled: 'Nenhum PIX cancelado'
        };
        return m[status] || 'Nenhuma transacao encontrada';
    }

    /* ---- Copy to clipboard ---- */
    function copyEl(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var text = el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' ? el.value : el.textContent;
        navigator.clipboard.writeText(text).then(function() { toast('success', 'Copiado'); }).catch(function() { toast('error', 'Erro ao copiar'); });
    }

    /* ---- Page Router ---- */
    function getPage() { return document.body.dataset.page || ''; }

    /* ---- Expose ---- */
    window.AstraPay = {
        auth: auth,
        api: { get: apiGet, post: apiPost, put: apiPut, delete: apiDelete },
        toast: toast,
        format: fmt,
        mask: mask,
        statusBadge: statusBadge,
        resendVerification: resendVerification
    };
    window.copyToClipboard = copyEl;

    /* ================================================================
       PAGE INITIALIZERS
       ================================================================ */

    function initLogin() {
        var f = $('#login-form'); if (!f) return;
        f.addEventListener('submit', async function(e) {
            e.preventDefault();
            var btn = $('#login-submit'), bt = $('#login-submit-text'), sp = $('#login-spinner'), er = $('#login-error');
            btn.disabled = true; bt.classList.add('hidden'); sp.classList.remove('hidden'); hi('#login-error');
            try {
                var r = await apiPost('/api/auth/login', { email: $('#login-email').value.trim(), password: $('#login-password').value });
                if (r.success && r.data && r.data.token) {
                    auth.token = r.data.token; auth.user = r.data.user;
                    toast('success', 'Login realizado com sucesso');
                    setTimeout(function() { window.location.href = '/dashboard'; }, 400);
                } else { throw new Error(r.error || 'Credenciais invalidas'); }
            } catch (err) { er.textContent = err.message; sh('#login-error'); }
            finally { btn.disabled = false; bt.classList.remove('hidden'); sp.classList.add('hidden'); }
        });
    }

    function initRegister() {
        var f = $('#register-form'); if (!f) return;
        var ci = $('#register-cpf'); if (ci) mask.cpf(ci);
        var pi = $('#register-phone'); if (pi) mask.phone(pi);
        f.addEventListener('submit', async function(e) {
            e.preventDefault();
            var btn = $('#register-submit'), bt = $('#register-submit-text'), sp = $('#register-spinner'), er = $('#register-error');
            btn.disabled = true; bt.classList.add('hidden'); sp.classList.remove('hidden'); hi('#register-error');

            var name = $('#register-name').value.trim();
            var email = $('#register-email').value.trim();
            var cpf = $('#register-cpf').value.trim();
            var pw = $('#register-password').value;
            var pwc = $('#register-password-confirm').value;

            if (pw !== pwc) { er.textContent = 'As senhas nao conferem'; sh('#register-error'); btn.disabled = false; bt.classList.remove('hidden'); sp.classList.add('hidden'); return; }
            if (pw.length < 8) { er.textContent = 'A senha deve ter no minimo 8 caracteres'; sh('#register-error'); btn.disabled = false; bt.classList.remove('hidden'); sp.classList.add('hidden'); return; }

            try {
                var payload = { name: name, email: email, password: pw, password_confirmation: pwc };
                if (cpf) payload.cpf = cpf;
                var r = await apiPost('/api/auth/register', payload);
                if (r.success && r.data && r.data.token) {
                    auth.token = r.data.token; auth.user = r.data.user;
                    toast('success', 'Conta criada com sucesso');
                    setTimeout(function() { window.location.href = '/dashboard'; }, 400);
                } else { throw new Error(r.error || r.message || 'Erro ao criar conta'); }
            } catch (err) { er.textContent = err.message; sh('#register-error'); }
            finally { btn.disabled = false; bt.classList.remove('hidden'); sp.classList.add('hidden'); }
        });
    }

    function initVerify() {
        var p = new URLSearchParams(window.location.search);
        var tok = p.get('token');
        if (!tok) { hi('#verify-loading'); $('#verify-error-msg').textContent = 'Token nao encontrado'; sh('#verify-error'); return; }
        apiPost('/api/auth/verify-email', { token: tok }).then(function(r) {
            if (r.success) { hi('#verify-loading'); sh('#verify-success'); setTimeout(function() { window.location.href = '/dashboard'; }, 2500); }
            else { throw new Error(r.error || 'Erro'); }
        }).catch(function(e) { hi('#verify-loading'); $('#verify-error-msg').textContent = e.message; sh('#verify-error'); });
    }

    async function initDashboard() {
        if (!auth.require()) return;

        try {
            var ur = await auth.me();
            if (ur && ur.data) {
                var u = (ur.data && ur.data.user) ? ur.data.user : ur.data; auth.user = u;
                $('#dash-user-name').textContent = u.name || 'Usuario';
                var gr = $('#dash-greeting-name'); if (gr) gr.textContent = u.name || 'Usuario';
                var av = $('#dash-user-avatar'); if (av && u.name) av.textContent = u.name.charAt(0).toUpperCase();
                var tb = $('#dash-user-tier');
                if (tb) { tb.textContent = auth.getTierLabel(u.tier || 'new'); tb.className = auth.getTierClass(u.tier || 'new'); }
                var em = $('#dash-user-email'); if (em) em.textContent = u.email || '';

                if (u.tier === 'new' && !u.email_verified) {
                    sh('#dash-verify-banner');
                }
            }
        } catch (e) { /* pass */ }

        try {
            var sr = await apiGet('/api/user/stats');
            if (sr.success && sr.data) {
                var s = sr.data;
                $('#stat-balance').textContent = fmt.brl(s.saldo || s.current_balance || 0);
                $('#stat-received').textContent = fmt.brl(s.total_recebido || s.total_received || 0);
                $('#stat-pix-count').textContent = fmt.int(s.pix_gerados || s.total_transactions || 0);
            }
        } catch (e) { /* pass */ }

        try {
            var tr = await apiGet('/api/pix/list?limit=10');
            if (tr.success && tr.data) renderTxTable(tr.data, '#dash-tx-table');
        } catch (e) { /* pass */ }

        try {
            var kr = await apiGet('/api/keys/list');
            var kc = $('#dash-api-key-count');
            if (kc) {
                var keyList = (kr && kr.data && kr.data.keys) ? kr.data.keys : [];
                if (keyList.length > 0) {
                    kc.textContent = 'Voce tem ' + keyList.length + ' chave(s) de API';
                } else {
                    kc.textContent = 'Voce ainda nao possui chaves de API';
                }
            }
        } catch (e) { /* pass */ }

        hi('#dash-loading'); sh('#dash-content');
    }

    function renderTxTable(txs, tid) {
        var tb = $(tid + ' tbody'), em = $(tid + '-empty');
        if (!tb) return;
        if (!txs || txs.length === 0) { tb.innerHTML = ''; if (em) sh(tid + '-empty'); return; }
        if (em) hi(tid + '-empty');
        var h = '';
        txs.forEach(function(tx) {
            h += '<tr><td class="mono" style="color:#666666;">' + fmt.sid(tx.id) + '</td>';
            h += '<td style="font-size:0.8125rem;color:#cccccc;">' + escHtml(tx.description || '-') + '</td>';
            h += '<td class="num">' + fmt.brl(tx.amount || tx.valor || 0) + '</td>';
            h += '<td>' + statusBadge(tx.status) + '</td>';
            h += '<td class="muted" style="font-size:0.75rem;">' + fmt.date(tx.created_at) + '</td></tr>';
        });
        tb.innerHTML = h;
    }

    function renderTxTableFull(txs) {
        var tb = $('#tx-table tbody'), em = $('#tx-table-empty');
        if (!tb) return;
        if (!txs || txs.length === 0) {
            tb.innerHTML = '';
            if (em) { em.classList.remove('hidden'); }
            $('#tx-empty-message').textContent = emptyMessages(txStatus);
            return;
        }
        if (em) em.classList.add('hidden');
        var h = '';
        txs.forEach(function(tx) {
            h += '<tr class="clickable-row" onclick="showTxDetail(' + tx.id + ')">';
            h += '<td class="mono" style="color:#666666;">' + fmt.sid(tx.id) + '</td>';
            h += '<td style="font-size:0.8125rem;color:#666666;">' + fmt.dateTime(tx.created_at) + '</td>';
            h += '<td style="font-size:0.8125rem;color:#cccccc;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + escHtml(tx.description || '-') + '</td>';
            h += '<td class="num">' + fmt.brl(tx.amount || tx.valor || 0) + '</td>';
            h += '<td>' + statusBadge(tx.status) + '</td></tr>';
        });
        tb.innerHTML = h;
    }

    window.showTxDetail = function(id) {
        apiGet('/api/pix/status?id=' + id).then(function(r) {
            if (r.success && r.data) {
                var tx = r.data;
                $('#tx-detail-id').textContent = fmt.sid(tx.id);
                $('#tx-detail-amount').textContent = fmt.brl(tx.amount || tx.valor || 0);
                $('#tx-detail-description').textContent = tx.description || '-';
                $('#tx-detail-date').textContent = fmt.dateTime(tx.created_at);
                $('#tx-detail-badge').innerHTML = statusBadge(tx.status);
                var code = tx.pix_copy_paste || tx.copy_paste || '';
                var qr = tx.qr_code || tx.pix_qrcode_url || tx.pixQrCodeUrl || '';
                if (code) { $('#tx-detail-code').textContent = code; sh('#tx-detail-code-block'); }
                else { hi('#tx-detail-code-block'); }
                if (qr) { $('#tx-detail-qr').src = qr; sh('#tx-detail-qr-block'); }
                else { hi('#tx-detail-qr-block'); }
                sh('#tx-detail-modal');
            }
        }).catch(function() { toast('error', 'Erro ao carregar detalhes'); });
    };

    window.closeTxDetail = function() { hi('#tx-detail-modal'); };

    function initPix() {
        if (!auth.require()) return;
        var ai = $('#pix-amount'); if (ai) mask.currency(ai);
        var f = $('#pix-form');
        if (!f) return;
        f.addEventListener('submit', async function(e) {
            e.preventDefault();
            var btn = $('#pix-submit'), bt = $('#pix-submit-text'), sp = $('#pix-spinner'), er = $('#pix-error');
            var val = mask.rawCur(ai), desc = $('#pix-description').value.trim();
            if (!val || val <= 0) { er.textContent = 'Informe um valor valido'; sh('#pix-error'); return; }
            btn.disabled = true; bt.classList.add('hidden'); sp.classList.remove('hidden'); hi('#pix-error'); hi('#pix-result');
            try {
                var r = await apiPost('/api/pix/create', { amount: val, description: desc || undefined });
                if (r.success && r.data) { showPixResult(r.data); }
                else { throw new Error(r.error || 'Erro ao gerar PIX'); }
            } catch (err) { er.textContent = err.message; sh('#pix-error'); }
            finally { btn.disabled = false; bt.classList.remove('hidden'); sp.classList.add('hidden'); }
        });
    }

    var pixPollingTimer = null;

    function showPixResult(data) {
        var tx = data.transaction || data;
        var amt = tx.amount || tx.valor || 0;
        var qr = tx.qr_code || tx.pix_qrcode_url || tx.pixQrCodeUrl || '';
        var cp = tx.copy_paste || tx.pix_copy_paste || '';
        $('#pix-result-amount').textContent = fmt.brl(amt);
        $('#pix-result-code').textContent = cp;
        $('#pix-result-id').textContent = fmt.sid(tx.id);
        if (qr) { $('#pix-result-qr').src = qr; sh('#pix-result-qr-container'); } else { hi('#pix-result-qr-container'); }
        hi('#pix-form-card'); sh('#pix-result');
        hi('#pix-success'); sh('#pix-polling');
        pollPix(tx.id);
    }

    function pollPix(txId) {
        if (pixPollingTimer) clearTimeout(pixPollingTimer);
        var attempts = 0;
        var maxAttempts = 120;
        function check() {
            apiGet('/api/pix/status?id=' + txId).then(function(r) {
                if (r.success && r.data) {
                    var st = r.data.status;
                    var badge = $('#pix-status-badge');
                    if (st === 'paid' || st === 'confirmed' || st === 'received') {
                        if (badge) { badge.textContent = 'Pago!'; badge.className = 'badge badge-success'; }
                        hi('#pix-polling'); sh('#pix-success');
                        toast('success', 'Pagamento recebido com sucesso!');
                        return;
                    }
                }
                attempts++;
                if (attempts >= maxAttempts) { return; }
            }).catch(function() {}).finally(function() {
                if (attempts < maxAttempts) { pixPollingTimer = setTimeout(check, 5000); }
            });
        }
        pixPollingTimer = setTimeout(check, 5000);
    }

    window.newPixForm = function() {
        if (pixPollingTimer) clearTimeout(pixPollingTimer);
        hi('#pix-result'); hi('#pix-success'); sh('#pix-form-card'); sh('#pix-polling');
        var ai = $('#pix-amount'); if (ai) ai.value = '';
        var di = $('#pix-description'); if (di) di.value = '';
    };

    var txStatus = 'all', txPage = 1;

    function loadTx(status, page) {
        status = status || 'all'; page = page || 1;
        txStatus = status; txPage = page;
        var url = '/api/pix/list?limit=20&page=' + page;
        if (status !== 'all') url += '&status=' + status;
        apiGet(url).then(function(r) {
            if (r.success) {
                renderTxTableFull(r.data || []);
                if (r.pagination) {
                    $('#tx-page-info').textContent = 'Pagina ' + r.pagination.page + ' de ' + r.pagination.total_pages;
                    $('#tx-prev').disabled = r.pagination.page <= 1;
                    $('#tx-next').disabled = r.pagination.page >= r.pagination.total_pages;
                }
            }
        }).catch(function() { toast('error', 'Erro ao carregar transacoes'); });
    }

    window.filterTransactions = function(st) {
        $$('.tx-filter-tab, .tab').forEach(function(t) { t.classList.remove('active'); });
        var at = document.querySelector('[data-status="' + st + '"]');
        if (at) { at.classList.add('active'); }
        loadTx(st, 1);
    };

    window.prevTxPage = function() { if (txPage > 1) loadTx(txStatus, txPage - 1); };
    window.nextTxPage = function() { loadTx(txStatus, txPage + 1); };

    function initTransactions() { if (!auth.require()) return; loadTx('all', 1); }

    function initSettings() {
        if (!auth.require()) return;
        var pi = $('#settings-phone'); if (pi) mask.phone(pi);
        apiGet('/api/auth/me').then(function(r) {
            if (r.success && r.data) {
                var u = (r.data && r.data.user) ? r.data.user : r.data; auth.user = u;
                $('#settings-name').value = u.name || '';
                $('#settings-email-display').textContent = u.email || '';
                $('#settings-phone').value = u.phone || '';
                $('#settings-cpf-display').textContent = u.cpf || 'Nao informado';
                $('#settings-pix-key').value = u.pix_key || '';
                if (u.pix_key_type) $('#settings-pix-type').value = u.pix_key_type;
                $('#settings-tier-badge').textContent = auth.getTierLabel(u.tier || 'new');
                var td = $('#settings-tier-display'); if (td) td.textContent = auth.getTierLabel(u.tier || 'new');
                if (u.name) { var an = $('#settings-avatar-letter'); if (an) an.textContent = u.name.charAt(0).toUpperCase(); }
                $('#settings-name-display').textContent = u.name || 'Usuario';
                if (u.email_verified) {
                    var ev = $('#settings-email-verified');
                    if (ev) { ev.classList.remove('hidden'); }
                }
            }
        }).catch(function() { toast('error', 'Erro ao carregar perfil'); });
    }

    window.saveProfile = function() {
        var name = $('#settings-name').value.trim();
        var phone = $('#settings-phone').value.trim();
        apiPost('/api/user/update-profile', { name: name, phone: phone }).then(function() {
            toast('success', 'Alteracoes salvas');
            $('#settings-name-display').textContent = name || 'Usuario';
            var an = $('#settings-avatar-letter'); if (an && name) an.textContent = name.charAt(0).toUpperCase();
            var dn = $('#dash-user-name'); if (dn) dn.textContent = name || 'Usuario';
            var gn = $('#dash-greeting-name'); if (gn) gn.textContent = name || 'Usuario';
        }).catch(function(e) { toast('error', e.message); });
    };

    window.savePixKey = function() {
        var key = $('#settings-pix-key').value.trim();
        var type = $('#settings-pix-type').value;
        if (!key) { toast('error', 'Informe sua chave PIX'); return; }
        apiPost('/api/user/update-pix-key', { pix_key: key, pix_key_type: type }).then(function() { toast('success', 'Chave PIX atualizada'); }).catch(function(e) { toast('error', e.message); });
    };

    window.changePassword = function() {
        var cur = $('#settings-pw-current').value, nw = $('#settings-pw-new').value, cf = $('#settings-pw-confirm').value;
        if (nw !== cf) { toast('error', 'As senhas nao conferem'); return; }
        if (nw.length < 8) { toast('error', 'A senha deve ter no minimo 8 caracteres'); return; }
        apiPut('/api/user/password', { current_password: cur, password: nw, password_confirmation: cf }).then(function() {
            toast('success', 'Senha alterada com sucesso');
            $('#settings-pw-current').value = ''; $('#settings-pw-new').value = ''; $('#settings-pw-confirm').value = '';
        }).catch(function(e) { toast('error', e.message); });
    };

    function resendVerification() {
        apiPost('/api/auth/send-verification', {}).then(function() { toast('success', 'Email de verificacao reenviado'); }).catch(function(e) { toast('error', e.message); });
    }

    window.toggleUserDropdown = function() { var d = $('#user-dropdown'); if (d) d.classList.toggle('hidden'); };

    window.switchSettingsTab = function(tab) {
        $$('.tab-btn').forEach(function(t) { t.classList.remove('active'); });
        $$('.settings-panel').forEach(function(p) { p.classList.add('hidden'); });
        var tel = document.querySelector('[data-settings-tab="' + tab + '"]');
        if (tel) { tel.classList.add('active'); }
        var pnl = $('#settings-panel-' + tab);
        if (pnl) { pnl.classList.remove('hidden'); }
    };

    /* ---- Init ---- */
    document.addEventListener('DOMContentLoaded', function() {
        var p = getPage();
        if (p === 'login') { initLogin(); initLoginNotifications(); }
        if (p === 'register') initRegister();
        if (p === 'verify-email') initVerify();
        if (p === 'dashboard') initDashboard();
        if (p === 'pix') initPix();
        if (p === 'transactions') initTransactions();
        if (p === 'settings') initSettings();

        document.addEventListener('click', function(e) {
            var d = $('#user-dropdown'), b = $('#user-dropdown-btn');
            if (d && !d.classList.contains('hidden') && b && !b.contains(e.target) && !d.contains(e.target)) d.classList.add('hidden');
        });

        var dm = $('#tx-detail-modal');
        if (dm) {
            dm.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-backdrop')) closeTxDetail();
            });
        }
    });


    /* ---- Login Notification Toasts ---- */
    var loginNotifications = [
        'Mais de 10.000 usuarios confiam na AstraPay',
        'PIX em menos de 5 segundos',
        'Sem taxa de manutencao - 0% para sempre',
        'Suporte 24h via chat',
        'Comprovante instantaneo a cada pagamento',
        'Integracao via API REST - dev friendly',
        'QR Code e Pix Copia e Cola',
        'Seus dados protegidos com criptografia',
        'Completo dashboard de transacoes'
    ];
    var _notifIndex = 0;
    var _notifTimer = null;

    function showLoginNotification() {
        var container = document.getElementById('toast-container');
        if (!container) return;
        var msg = loginNotifications[_notifIndex % loginNotifications.length];
        _notifIndex++;
        var el = document.createElement('div');
        el.style.cssText = 'background:#0a0a0a;border:1px solid #141414;color:#aaaaaa;padding:0.5rem 0.875rem;border-radius:8px;font-size:0.75rem;pointer-events:auto;animation:floatUpNotify 4s ease-out forwards;opacity:0;transform:translateY(10px);max-width:320px;box-shadow:0 4px 24px rgba(0,0,0,0.5);';
        el.textContent = msg;
        container.appendChild(el);
        requestAnimationFrame(function() { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
        setTimeout(function() {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 300);
        }, 3500);
    }

    function startLoginNotifications() {
        showLoginNotification();
        _notifTimer = setInterval(showLoginNotification, 6000);
    }

    function stopLoginNotifications() {
        if (_notifTimer) { clearInterval(_notifTimer); _notifTimer = null; }
    }

    function initLoginNotifications() {
        var loginInputs = document.querySelectorAll('#login-email, #login-password');
        loginInputs.forEach(function(inp) {
            inp.addEventListener('focus', function() { stopLoginNotifications(); });
        });
        startLoginNotifications();
    }

})();
