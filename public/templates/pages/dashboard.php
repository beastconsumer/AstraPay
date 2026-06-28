<div id="dash-loading" style="display:flex;flex-direction:column;gap:24px;">
    <div class="grid-stats">
        <div class="card" style="height:120px;"></div>
        <div class="card" style="height:120px;"></div>
        <div class="card" style="height:120px;"></div>
    </div>
</div>

<div id="dash-content" class="hidden animate-fade-in-up">

    <div class="page-header">
        <div>
            <h1>Ola, <span id="dash-greeting-name" style="font-family:'Space Grotesk',sans-serif;">Usuario</span></h1>
            <p class="page-subtitle">Bem-vindo ao seu painel</p>
        </div>
        <div style="display:flex;align-items:center;gap:16px;">
            <span id="dash-user-tier" class="badge-tier">Novo</span>
            <a href="/pix" class="astra-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Gerar PIX
            </a>
        </div>
    </div>

    <div id="dash-verify-banner" class="hidden banner banner-warning" style="margin-bottom:24px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div style="flex:1;">
            <p style="font-size:0.875rem;color:#f59e0b;font-weight:500;">Verifique seu email para liberar todos os recursos</p>
            <p style="font-size:0.75rem;color:#888888;margin-top:2px;">Enviamos um link de confirmacao para seu email</p>
        </div>
        <button onclick="AstraPay.resendVerification()" class="btn-secondary" style="font-size:0.75rem;">Reenviar email</button>
    </div>

    <div class="grid-stats" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(255,255,255,0.03);border:1px solid #141414;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#888888" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <p class="stat-label">Saldo Atual</p>
            <p id="stat-balance" class="stat-value">R$ 0,00</p>
            <p class="stat-sub">Disponivel para saque</p>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(255,255,255,0.03);border:1px solid #141414;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#888888" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M6 4v16"/><path d="M18 4v16"/></svg>
            </div>
            <p class="stat-label">PIX Hoje</p>
            <p id="stat-pix-count" class="stat-value">0</p>
            <p class="stat-sub">gerados hoje</p>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(255,255,255,0.03);border:1px solid #141414;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#888888" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <p class="stat-label">Total Recebido</p>
            <p id="stat-received" class="stat-value">R$ 0,00</p>
            <p class="stat-sub">acumulado</p>
        </div>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="align-items:flex-start;">
            <div>
                <h3 style="font-size:1rem;font-weight:600;color:#ffffff;font-family:'Space Grotesk',sans-serif;margin:0;">API Keys</h3>
                <p style="font-size:0.8125rem;color:#888;margin-top:4px;">Gerencie chaves para integrar com a API REST</p>
            </div>
            <a href="/api-keys" class="btn-secondary" style="font-size:0.8125rem;padding:10px 18px;height:auto;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                Gerenciar API Keys
            </a>
        </div>
        <p id="dash-api-key-count" style="font-size:0.8125rem;color:#888;">Carregando...</p>
    </div>

    <div class="card" style="overflow:hidden;padding:0;">
        <div class="card-header" style="padding:20px 24px 16px;">
            <h3 style="font-size:1rem;font-weight:600;color:#ffffff;font-family:'Space Grotesk',sans-serif;margin:0;">Transacoes Recentes</h3>
            <a href="/transactions" class="btn-ghost">Ver todas</a>
        </div>

        <table id="dash-tx-table" class="table" style="margin-bottom:0;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descricao</th>
                    <th style="text-align:right;">Valor</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div id="dash-tx-table-empty" class="hidden empty-state">
            <div class="empty-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666666" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <p class="empty-title">Nenhuma transacao ainda</p>
            <p class="empty-sub">Gere seu primeiro PIX para comecar</p>
            <a href="/pix" class="astra-btn">Gerar PIX</a>
        </div>
    </div>

</div>
