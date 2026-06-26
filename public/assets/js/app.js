/*
 *  AstraPay - Frontend Application
 *  Auth, API, Toast, Format, Mask utilities
 */

(function () {
    'use strict';

    const API_BASE = '';

    /* ---- Auth ---- */
    const auth = {
        get token() {
            return localStorage.getItem('astrapay_token');
        },
        set token(val) {
            if (val) localStorage.setItem('astrapay_token', val);
            else localStorage.removeItem('astrapay_token');
        },
        get user() {
            try {
                const u = localStorage.getItem('astrapay_user');
                return u ? JSON.parse(u) : null;
            } catch (e) {
                return null;
            }
        },
        set user(val) {
            if (val) localStorage.setItem('astrapay_user', JSON.stringify(val));
            else localStorage.removeItem('astrapay_user');
        },
        isAuthenticated() {
            return !!auth.token;
        },
        require() {
            if (!auth.isAuthenticated()) {
                window.location.href = '/login';
                return false;
            }
            return true;
        },
        async me() {
            return apiGet('/api/auth/me');
        },
        logout() {
            auth.token = null;
            auth.user = null;
            window.location.href = '/';
        },
        getTierLabel(tier) {
            const labels = {
                'new': 'Novo',
                'basic': 'Basico',
                'bronze': 'Bronze',
                'silver': 'Prata',
                'gold': 'Ouro'
            };
            return labels[tier] || tier;
        },
        getTierClass(tier) {
            const classes = {
                'new': 'bg-zinc-800 text-zinc-400 border border-zinc-700',
                'basic': 'bg-zinc-800 text-zinc-300 border border-zinc-700',
                'bronze': 'tier-bronze',
                'silver': 'tier-silver',
                'gold': 'tier-gold'
            };
            return classes[tier] || 'bg-zinc-800 text-zinc-400 border border-zinc-700';
        }
    };

    /* ---- API Helpers ---- */
    async function apiFetch(path, options = {}) {
        const url = API_BASE + path;
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers
        };

        if (auth.token) {
            headers['Authorization'] = 'Bearer ' + auth.token;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json().catch(() => null);

            if (!response.ok) {
                const msg = (data && data.error) || data.message || 'Erro na requisicao';
                throw new Error(msg);
            }

            return data;
        } catch (err) {
            if (err.name === 'TypeError' && err.message.includes('fetch')) {
                throw new Error('Erro de conexao. Verifique sua internet.');
            }
            throw err;
        }
    }

    async function apiGet(path) {
        return apiFetch(path, { method: 'GET' });
    }

    async function apiPost(path, data) {
        return apiFetch(path, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async function apiPut(path, data) {
        return apiFetch(path, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async function apiDelete(path) {
        return apiFetch(path, { method: 'DELETE' });
    }

    /* ---- Toast System ---- */
    const toastContainer = document.getElementById('toast-container');

    function createToast(type, message, duration = 4000) {
        const icons = {
            success: '<svg class="w-5 h-5 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            error: '<svg class="w-5 h-5 text-red-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg class="w-5 h-5 text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg class="w-5 h-5 text-violet-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };

        const el = document.createElement('div');
        el.className = 'animate-toast-in bg-zinc-900 border border-zinc-800 rounded-lg p-4 shadow-lg flex items-start gap-3 max-w-sm';
        el.innerHTML = `
            ${icons[type] || icons.info}
            <div class="flex-1 min-w-0">
                <p class="text-sm text-zinc-100">${escapeHtml(message)}</p>
            </div>
            <button class="text-zinc-500 hover:text-zinc-300 shrink-0" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;

        if (toastContainer) {
            toastContainer.appendChild(el);
            setTimeout(() => {
                el.classList.remove('animate-toast-in');
                el.classList.add('animate-toast-out');
                setTimeout(() => el.remove(), 200);
            }, duration);
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    const toast = {
        success: (msg) => createToast('success', msg),
        error: (msg) => createToast('error', msg, 6000),
        warning: (msg) => createToast('warning', msg, 5000),
        info: (msg) => createToast('info', msg, 3000)
    };

    /* ---- Format Utilities ---- */
    const format = {
        brl(value) {
            const num = parseFloat(value) || 0;
            return 'R$ ' + num.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        date(iso) {
            if (!iso) return '-';
            const d = new Date(iso + (iso.includes('T') ? '' : 'T00:00:00'));
            if (isNaN(d.getTime())) return iso;
            return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        dateTime(iso) {
            if (!iso) return '-';
            const d = new Date(iso + (iso.includes('T') ? '' : 'T00:00:00'));
            if (isNaN(d.getTime())) return iso;
            return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        },
        integer(num) {
            return parseInt(num, 10).toLocaleString('pt-BR');
        },
        shortId(id) {
            return '#' + String(id).padStart(6, '0');
        }
    };

    /* ---- Mask Utilities ---- */
    const mask = {
        cpf(input) {
            input.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '').substring(0, 11);
                if (v.length > 9) v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
                else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{3}).*/, '$1.$2.$3');
                else if (v.length > 3) v = v.replace(/^(\d{3})(\d{3}).*/, '$1.$2');
                this.value = v;
            });
        },
        phone(input) {
            input.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '').substring(0, 11);
                if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
                this.value = v;
            });
        },
        currency(input) {
            input.addEventListener('input', function () {
                let v = this.value.replace(/\D/g, '');
                if (!v) { this.value = ''; return; }
                const num = parseFloat(v) / 100;
                this.value = num.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            });
            input.addEventListener('blur', function () {
                const num = parseFloat(this.value.replace(/\./g, '').replace(',', '.')) || 0;
                if (num > 0) {
                    this.value = num.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            });
        },
        getRawCurrency(input) {
            const raw = input.value.replace(/\./g, '').replace(',', '.');
            return parseFloat(raw) || 0;
        }
    };

    /* ---- DOM Helpers ---- */
    function $(selector, parent) {
        return (parent || document).querySelector(selector);
    }

    function $$(selector, parent) {
        return Array.from((parent || document).querySelectorAll(selector));
    }

    function setHtml(selector, html) {
        const el = $(selector);
        if (el) el.innerHTML = html;
    }

    function setText(selector, text) {
        const el = $(selector);
        if (el) el.textContent = text;
    }

    function show(selector) {
        const el = $(selector);
        if (el) el.classList.remove('hidden');
    }

    function hide(selector) {
        const el = $(selector);
        if (el) el.classList.add('hidden');
    }

    function toggle(selector) {
        const el = $(selector);
        if (el) el.classList.toggle('hidden');
    }

    /* ---- Status Badge ---- */
    function statusBadge(status) {
        const map = {
            'pending': { label: 'Pendente', cls: 'bg-amber-500/10 text-amber-400 border border-amber-500/20' },
            'confirmed': { label: 'Confirmado', cls: 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' },
            'received': { label: 'Recebido', cls: 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' },
            'paid': { label: 'Pago', cls: 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' },
            'refunded': { label: 'Estornado', cls: 'bg-red-500/10 text-red-400 border border-red-500/20' },
            'cancelled': { label: 'Cancelado', cls: 'bg-zinc-800 text-zinc-400 border border-zinc-700' },
            'expired': { label: 'Expirado', cls: 'bg-zinc-800 text-zinc-400 border border-zinc-700' },
            'held': { label: 'Em Revisao', cls: 'bg-red-500/10 text-red-400 border border-red-500/20' },
            'processing': { label: 'Processando', cls: 'bg-violet-500/10 text-violet-400 border border-violet-500/20' },
            'completed': { label: 'Concluido', cls: 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' },
            'failed': { label: 'Falhou', cls: 'bg-red-500/10 text-red-400 border border-red-500/20' }
        };

        const s = map[status] || { label: status, cls: 'bg-zinc-800 text-zinc-400 border border-zinc-700' };
        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' + s.cls + '">' + s.label + '</span>';
    }

    /* ---- Redirect ---- */
    function redirect(path) {
        window.location.href = path;
    }

    /* ---- Page Router ---- */
    function getCurrentPage() {
        return document.body.dataset.page || '';
    }

    /* ---- Expose Globals ---- */
    window.AstraPay = {
        auth,
        api: { get: apiGet, post: apiPost, put: apiPut, delete: apiDelete },
        toast,
        format,
        mask,
        dom: { $, $$, setHtml, setText, show, hide, toggle },
        statusBadge,
        redirect,
        getCurrentPage
    };

    /* ================================================================
       PAGE INITIALIZATION
       ================================================================ */

    function initLoginPage() {
        const form = $('#login-form');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = $('#login-submit');
            const btnText = $('#login-submit-text');
            const spinner = $('#login-spinner');
            const errorEl = $('#login-error');

            btn.disabled = true;
            btnText.classList.add('hidden');
            spinner.classList.remove('hidden');
            hide('#login-error');

            const email = $('#login-email').value.trim();
            const password = $('#login-password').value;

            try {
                const res = await apiPost('/api/auth/login', { email, password });
                if (res.success && res.data && res.data.token) {
                    auth.token = res.data.token;
                    auth.user = res.data.user;
                    toast.success('Login realizado com sucesso!');
                    setTimeout(() => redirect('/dashboard'), 500);
                } else {
                    throw new Error(res.error || 'Credenciais invalidas');
                }
            } catch (err) {
                setText('#login-error', err.message);
                show('#login-error');
            } finally {
                btn.disabled = false;
                btnText.classList.remove('hidden');
                spinner.classList.add('hidden');
            }
        });
    }

    function initRegisterPage() {
        const form = $('#register-form');
        if (!form) return;

        const cpfInput = $('#register-cpf');
        if (cpfInput) mask.cpf(cpfInput);

        const phoneInput = $('#register-phone');
        if (phoneInput) mask.phone(phoneInput);

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = $('#register-submit');
            const btnText = $('#register-submit-text');
            const spinner = $('#register-spinner');
            const errorEl = $('#register-error');

            btn.disabled = true;
            btnText.classList.add('hidden');
            spinner.classList.remove('hidden');
            hide('#register-error');

            const name = $('#register-name').value.trim();
            const email = $('#register-email').value.trim();
            const cpf = $('#register-cpf').value.trim();
            const phone = $('#register-phone').value.trim();
            const password = $('#register-password').value;
            const passwordConfirmation = $('#register-password-confirm').value;

            if (password !== passwordConfirmation) {
                setText('#register-error', 'As senhas nao conferem');
                show('#register-error');
                btn.disabled = false;
                btnText.classList.remove('hidden');
                spinner.classList.add('hidden');
                return;
            }

            if (password.length < 8) {
                setText('#register-error', 'A senha deve ter no minimo 8 caracteres');
                show('#register-error');
                btn.disabled = false;
                btnText.classList.remove('hidden');
                spinner.classList.add('hidden');
                return;
            }

            try {
                const payload = { name, email, password, password_confirmation: passwordConfirmation };
                if (cpf) payload.cpf = cpf;
                if (phone) payload.phone = phone;

                const res = await apiPost('/api/auth/register', payload);
                if (res.success && res.data && res.data.token) {
                    auth.token = res.data.token;
                    auth.user = res.data.user;
                    toast.success('Conta criada com sucesso!');
                    setTimeout(() => redirect('/dashboard'), 500);
                } else {
                    throw new Error(res.error || res.message || 'Erro ao criar conta');
                }
            } catch (err) {
                setText('#register-error', err.message);
                show('#register-error');
            } finally {
                btn.disabled = false;
                btnText.classList.remove('hidden');
                spinner.classList.add('hidden');
            }
        });
    }

    function initVerifyEmailPage() {
        const tokenEl = $('#verify-token');
        const successEl = $('#verify-success');
        const errorEl = $('#verify-error');
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        if (!token) {
            setText('#verify-error', 'Token nao encontrado na URL');
            show('#verify-error');
            if (tokenEl) hide('#verify-loading');
            return;
        }

        (async function () {
            try {
                const res = await apiPost('/api/auth/verify-email', { token });
                if (res.success) {
                    hide('#verify-loading');
                    show('#verify-success');
                    toast.success('Email verificado com sucesso!');
                    setTimeout(() => redirect('/dashboard'), 3000);
                } else {
                    throw new Error(res.error || 'Erro ao verificar email');
                }
            } catch (err) {
                hide('#verify-loading');
                setText('#verify-error', err.message);
                show('#verify-error');
            }
        })();
    }

    async function initDashboardPage() {
        if (!auth.require()) return;

        const user = await auth.me().catch(() => null);

        if (user && user.data) {
            const u = user.data;
            auth.user = u;

            setText('#dash-user-name', u.name || 'Usuario');
            setText('#dash-user-email', u.email || '');
            setText('#dash-user-tier', auth.getTierLabel(u.tier || 'new'));
            const tierBadge = $('#dash-user-tier');
            if (tierBadge) {
                tierBadge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ' + auth.getTierClass(u.tier || 'new');
            }

            const avatarEl = $('#dash-user-avatar');
            if (avatarEl && u.name) {
                avatarEl.textContent = u.name.charAt(0).toUpperCase();
            }

            if (u.email_verified === 0 || u.email_verified === '0') {
                show('#dash-verify-banner');
            }
        }

        try {
            const statsRes = await apiGet('/api/user/stats');
            if (statsRes.success && statsRes.data) {
                const s = statsRes.data;
                setText('#stat-balance', format.brl(s.saldo || s.current_balance || 0));
                setText('#stat-received', format.brl(s.total_recebido || s.total_received || 0));
                setText('#stat-pix-count', format.integer(s.pix_gerados || s.total_transactions || 0));
                setText('#stat-fee', (s.fee_pct || s.admin_fee_pct || 0) + '%');
            }
        } catch (e) {
            console.error('Stats error:', e);
        }

        try {
            const txRes = await apiGet('/api/pix/list?limit=10');
            if (txRes.success && txRes.data) {
                renderTransactionTable(txRes.data, '#dash-tx-table');
            }
        } catch (e) {
            console.error('Transactions error:', e);
        }

        hide('#dash-loading');
        show('#dash-content');

        loadDashboardChart();
    }

    function renderTransactionTable(transactions, tableId) {
        const tbody = $(tableId + ' tbody');
        const emptyEl = $(tableId + '-empty');
        if (!tbody) return;

        if (!transactions || transactions.length === 0) {
            if (tbody) tbody.innerHTML = '';
            if (emptyEl) show(tableId + '-empty');
            return;
        }

        if (emptyEl) hide(tableId + '-empty');

        let html = '';
        transactions.forEach(tx => {
            html += `
                <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/30 transition-colors">
                    <td class="px-4 py-3 text-xs font-mono text-zinc-500">${format.shortId(tx.id)}</td>
                    <td class="px-4 py-3 text-sm text-zinc-300">${escapeHtml(tx.description || '-')}</td>
                    <td class="px-4 py-3 text-sm font-mono tabular-nums text-right text-zinc-100">${format.brl(tx.amount || tx.valor || 0)}</td>
                    <td class="px-4 py-3">${statusBadge(tx.status)}</td>
                    <td class="px-4 py-3 text-sm text-zinc-500">${format.date(tx.created_at)}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function loadDashboardChart() {
        const chartCanvas = $('#dash-chart');
        if (!chartCanvas) return;

        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = () => drawBarChart(chartCanvas);
            script.onerror = () => { hide('#dash-chart-container'); };
            document.head.appendChild(script);
        } else {
            drawBarChart(chartCanvas);
        }
    }

    function drawBarChart(canvas) {
        fetch(API_BASE + '/api/user/stats')
            .then(r => r.json())
            .then(data => {
                const daily = (data.success && data.data && data.data.daily_volume) ? data.data.daily_volume : [];
                const labels = [];
                const values = [];
                const last7 = daily.slice(-7);

                if (last7.length === 0) {
                    for (let i = 6; i >= 0; i--) {
                        const d = new Date();
                        d.setDate(d.getDate() - i);
                        labels.push(d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }));
                        values.push(0);
                    }
                } else {
                    last7.forEach(d => {
                        const date = new Date(d.date + 'T00:00:00');
                        labels.push(date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }));
                        values.push(d.amount || 0);
                    });
                }

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Volume (R$)',
                            data: values,
                            backgroundColor: 'rgba(139, 92, 246, 0.5)',
                            borderColor: 'rgba(139, 92, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(39, 39, 42, 0.5)' },
                                ticks: { color: '#a1a1aa', font: { family: 'Inter' } }
                            },
                            y: {
                                grid: { color: 'rgba(39, 39, 42, 0.5)' },
                                ticks: {
                                    color: '#a1a1aa',
                                    font: { family: 'Inter' },
                                    callback: v => 'R$ ' + v
                                }
                            }
                        }
                    }
                });
            })
            .catch(() => {
                hide('#dash-chart-container');
            });
    }

    function initPixPage() {
        if (!auth.require()) return;

        const amountInput = $('#pix-amount');
        if (amountInput) mask.currency(amountInput);

        const form = $('#pix-form');
        if (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const btn = $('#pix-submit');
                const btnText = $('#pix-submit-text');
                const spinner = $('#pix-spinner');
                const errorEl = $('#pix-error');

                const value = mask.getRawCurrency(amountInput);
                const desc = $('#pix-description').value.trim();

                if (!value || value <= 0) {
                    setText('#pix-error', 'Informe um valor valido');
                    show('#pix-error');
                    return;
                }

                btn.disabled = true;
                btnText.classList.add('hidden');
                spinner.classList.remove('hidden');
                hide('#pix-error');
                hide('#pix-result');

                try {
                    const res = await apiPost('/api/pix/create', {
                        valor: value,
                        descricao: desc || undefined
                    });

                    if (res.success && res.data) {
                        showPixResult(res.data);
                    } else {
                        throw new Error(res.error || 'Erro ao gerar PIX');
                    }
                } catch (err) {
                    setText('#pix-error', err.message);
                    show('#pix-error');
                } finally {
                    btn.disabled = false;
                    btnText.classList.remove('hidden');
                    spinner.classList.add('hidden');
                }
            });
        }
    }

    function showPixResult(data) {
        const tx = data.transaction || data;
        const amount = tx.amount || tx.valor || 0;
        const qrUrl = tx.qr_code || tx.pix_qrcode_url || tx.pixQrCodeUrl || '';
        const copyPaste = tx.copy_paste || tx.pix_copy_paste || '';

        setText('#pix-result-amount', format.brl(amount));
        setText('#pix-result-code', copyPaste);
        setText('#pix-result-id', format.shortId(tx.id));

        if (qrUrl) {
            $('#pix-result-qr').src = qrUrl;
            show('#pix-result-qr-container');
        } else {
            hide('#pix-result-qr-container');
        }

        hide('#pix-form-card');
        show('#pix-result');

        pollPixStatus(tx.id);
    }

    function pollPixStatus(txId) {
        const checkStatus = async () => {
            try {
                const res = await apiGet('/api/pix/status?id=' + txId);
                if (res.success && res.data) {
                    const status = res.data.status;
                    setText('#pix-status-badge', status);

                    if (status === 'paid' || status === 'confirmed' || status === 'received') {
                        hide('#pix-polling');
                        show('#pix-success');
                        toast.success('Pagamento recebido!');
                        return;
                    }
                }
            } catch (e) {
                // continue polling
            }

            setTimeout(checkStatus, 3000);
        };

        setTimeout(checkStatus, 3000);
    }

    function initTransactionsPage() {
        if (!auth.require()) return;
        loadTransactions();
    }

    async function loadTransactions(statusFilter, page) {
        statusFilter = statusFilter || 'all';
        page = page || 1;

        let url = '/api/pix/list?limit=20&page=' + page;
        if (statusFilter !== 'all') url += '&status=' + statusFilter;

        try {
            const res = await apiGet(url);
            if (res.success) {
                const txList = res.data || [];
                renderTransactionTableFull(txList);
                if (res.pagination) {
                    setText('#tx-page-info', 'Pagina ' + res.pagination.page + ' de ' + res.pagination.total_pages);
                    $('#tx-prev').disabled = res.pagination.page <= 1;
                    $('#tx-next').disabled = res.pagination.page >= res.pagination.total_pages;
                }
            }
        } catch (e) {
            toast.error('Erro ao carregar transacoes');
        }
    }

    function renderTransactionTableFull(transactions) {
        const tbody = $('#tx-table tbody');
        const emptyEl = $('#tx-table-empty');
        if (!tbody) return;

        if (!transactions || transactions.length === 0) {
            tbody.innerHTML = '';
            if (emptyEl) emptyEl.classList.remove('hidden');
            return;
        }

        if (emptyEl) emptyEl.classList.add('hidden');

        let html = '';
        transactions.forEach(tx => {
            html += `
                <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/30 transition-colors cursor-pointer" onclick="showTxDetail(${tx.id})">
                    <td class="px-4 py-3 text-xs font-mono text-zinc-500">${format.shortId(tx.id)}</td>
                    <td class="px-4 py-3 text-sm text-zinc-400">${format.dateTime(tx.created_at)}</td>
                    <td class="px-4 py-3 text-sm text-zinc-300 max-w-[200px] truncate">${escapeHtml(tx.description || '-')}</td>
                    <td class="px-4 py-3 text-sm font-mono tabular-nums text-right text-zinc-100">${format.brl(tx.amount || tx.valor || 0)}</td>
                    <td class="px-4 py-3">${statusBadge(tx.status)}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    window.showTxDetail = function (txId) {
        const url = '/api/pix/status?id=' + txId;
        apiGet(url).then(res => {
            if (res.success && res.data) {
                const tx = res.data;
                const modal = $('#tx-detail-modal');
                if (!modal) return;

                setText('#tx-detail-id', format.shortId(tx.id));
                setText('#tx-detail-amount', format.brl(tx.amount || tx.valor || 0));
                setText('#tx-detail-status', '');
                setHtml('#tx-detail-badge', statusBadge(tx.status));
                setText('#tx-detail-description', tx.description || '-');
                setText('#tx-detail-date', format.dateTime(tx.created_at));

                const code = tx.copy_paste || tx.pix_copy_paste || '';
                if (code) {
                    setText('#tx-detail-code', code);
                    show('#tx-detail-code-block');
                } else {
                    hide('#tx-detail-code-block');
                }

                show('#tx-detail-modal');
            }
        }).catch(() => toast.error('Erro ao carregar detalhes'));
    };

    window.closeTxDetail = function () {
        hide('#tx-detail-modal');
    };

    window.copyToClipboard = function (elementId) {
        const el = document.getElementById(elementId);
        if (!el) return;
        navigator.clipboard.writeText(el.textContent).then(() => {
            toast.success('Copiado para a area de transferencia');
        }).catch(() => {
            toast.error('Erro ao copiar');
        });
    };

    function initSettingsPage() {
        if (!auth.require()) return;
        loadSettingsData();
    }

    async function loadSettingsData() {
        try {
            const res = await apiGet('/api/auth/me');
            if (res.success && res.data) {
                const u = res.data;
                auth.user = u;

                $('#settings-name').value = u.name || '';
                setText('#settings-email-display', u.email || '');
                $('#settings-phone').value = u.phone || '';
                $('#settings-cpf-display').textContent = u.cpf || 'Nao informado';
                $('#settings-pix-key').value = u.pix_key || '';
                if (u.pix_key_type) {
                    $('#settings-pix-type').value = u.pix_key_type;
                }

                setText('#settings-tier-badge', auth.getTierLabel(u.tier || 'new'));
                const tierEl = $('#settings-tier-badge');
                if (tierEl) {
                    tierEl.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ' + auth.getTierClass(u.tier || 'new');
                }

                if (u.email_verified) {
                    show('#settings-email-verified');
                }
            }
        } catch (e) {
            toast.error('Erro ao carregar perfil');
        }
    }

    window.saveProfile = async function () {
        const name = $('#settings-name').value.trim();
        const phone = $('#settings-phone').value.trim();

        try {
            await apiPut('/api/user/profile', { name, phone });
            toast.success('Perfil atualizado com sucesso!');
        } catch (e) {
            toast.error(e.message);
        }
    };

    window.savePixKey = async function () {
        const key = $('#settings-pix-key').value.trim();
        const type = $('#settings-pix-type').value;

        if (!key) {
            toast.error('Informe sua chave PIX');
            return;
        }

        try {
            await apiPost('/api/user/update-pix-key', {
                pix_key: key,
                pix_key_type: type
            });
            toast.success('Chave PIX atualizada com sucesso!');
        } catch (e) {
            toast.error(e.message);
        }
    };

    window.changePassword = async function () {
        const current = $('#settings-pw-current').value;
        const newPw = $('#settings-pw-new').value;
        const confirm = $('#settings-pw-confirm').value;

        if (newPw !== confirm) {
            toast.error('As senhas nao conferem');
            return;
        }
        if (newPw.length < 8) {
            toast.error('A senha deve ter no minimo 8 caracteres');
            return;
        }

        try {
            await apiPut('/api/user/password', {
                current_password: current,
                password: newPw,
                password_confirmation: confirm
            });
            toast.success('Senha alterada com sucesso!');
            $('#settings-pw-current').value = '';
            $('#settings-pw-new').value = '';
            $('#settings-pw-confirm').value = '';
        } catch (e) {
            toast.error(e.message);
        }
    };

    window.resendVerification = async function () {
        try {
            await apiPost('/api/auth/send-verification', {});
            toast.success('Email de verificacao reenviado!');
        } catch (e) {
            toast.error(e.message);
        }
    };

    /* ---- Sidebar Mobile Toggle ---- */
    window.toggleSidebar = function () {
        const sidebar = $('#app-sidebar');
        const overlay = $('#sidebar-overlay');
        if (sidebar) sidebar.classList.toggle('-translate-x-full');
        if (overlay) overlay.classList.toggle('hidden');
    };

    /* ---- Filter Tabs on Transactions Page ---- */
    window.filterTransactions = function (status) {
        $$('.tx-filter-tab').forEach(tab => {
            tab.classList.remove('tab-active', 'text-zinc-100');
            tab.classList.add('text-zinc-400');
        });
        const activeTab = document.querySelector('[data-status="' + status + '"]');
        if (activeTab) {
            activeTab.classList.add('tab-active', 'text-zinc-100');
            activeTab.classList.remove('text-zinc-400');
        }
        loadTransactions(status, 1);
    };

    window.prevTxPage = function () {
        // track current page state via data attribute
        const container = $('#tx-table-container');
        if (container) {
            const page = parseInt(container.dataset.page || '1') - 1;
            const status = container.dataset.status || 'all';
            if (page >= 1) {
                container.dataset.page = page;
                loadTransactions(status, page);
            }
        }
    };

    window.nextTxPage = function () {
        const container = $('#tx-table-container');
        if (container) {
            const page = parseInt(container.dataset.page || '1') + 1;
            const status = container.dataset.status || 'all';
            container.dataset.page = page;
            loadTransactions(status, page);
        }
    };

    /* ---- Generate New PIX button on result page ---- */
    window.newPixForm = function () {
        hide('#pix-result');
        hide('#pix-success');
        show('#pix-form-card');
        show('#pix-polling');
        const amountInput = $('#pix-amount');
        if (amountInput) amountInput.value = '';
        const descInput = $('#pix-description');
        if (descInput) descInput.value = '';
    };

    /* ---- Settings Tab Switching ---- */
    window.switchSettingsTab = function (tabName) {
        $$('.settings-tab').forEach(t => t.classList.remove('tab-active', 'text-zinc-100'));
        $$('.settings-panel').forEach(p => p.classList.add('hidden'));

        const tabEl = document.querySelector('[data-settings-tab="' + tabName + '"]');
        if (tabEl) {
            tabEl.classList.add('tab-active', 'text-zinc-100');
        }

        const panel = $('#settings-panel-' + tabName);
        if (panel) panel.classList.remove('hidden');
    };

    /* ---- User dropdown toggle ---- */
    window.toggleUserDropdown = function () {
        const dd = $('#user-dropdown');
        if (dd) dd.classList.toggle('hidden');
    };

    /* ---- Initialize ---- */
    document.addEventListener('DOMContentLoaded', function () {
        const page = getCurrentPage();

        if (page === 'login') initLoginPage();
        if (page === 'register') initRegisterPage();
        if (page === 'verify-email') initVerifyEmailPage();
        if (page === 'dashboard') initDashboardPage();
        if (page === 'pix') initPixPage();
        if (page === 'transactions') initTransactionsPage();
        if (page === 'settings') initSettingsPage();

        // Close dropdown on outside click
        document.addEventListener('click', function (e) {
            const dd = $('#user-dropdown');
            const btn = $('#user-dropdown-btn');
            if (dd && !dd.classList.contains('hidden') && btn && !btn.contains(e.target) && !dd.contains(e.target)) {
                dd.classList.add('hidden');
            }
        });

        // Close sidebar on overlay click
        const overlay = $('#sidebar-overlay');
        if (overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }
    });

})();
