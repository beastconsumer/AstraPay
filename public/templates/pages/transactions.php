<div id="tx-table-container" data-page="1" data-status="all">

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:0.875rem 1rem;border-bottom:1px solid #141414;">
            <div style="display:flex;gap:1.5rem;overflow-x:auto;">
                <button data-status="all" class="tx-filter-tab tab-active" style="font-size:0.8125rem;font-weight:500;color:#ffffff;background:none;border:none;border-bottom:2px solid #fff;padding-bottom:0.5rem;cursor:pointer;font-family:'Inter',system-ui,sans-serif;margin-bottom:-1px;" onclick="filterTransactions('all')">Todas</button>
                <button data-status="pending" class="tx-filter-tab" style="font-size:0.8125rem;font-weight:500;color:#666;background:none;border:none;padding-bottom:0.5rem;cursor:pointer;font-family:'Inter',system-ui,sans-serif;" onclick="filterTransactions('pending')">Pendentes</button>
                <button data-status="confirmed" class="tx-filter-tab" style="font-size:0.8125rem;font-weight:500;color:#666;background:none;border:none;padding-bottom:0.5rem;cursor:pointer;font-family:'Inter',system-ui,sans-serif;" onclick="filterTransactions('confirmed')">Confirmados</button>
                <button data-status="cancelled" class="tx-filter-tab" style="font-size:0.8125rem;font-weight:500;color:#666;background:none;border:none;padding-bottom:0.5rem;cursor:pointer;font-family:'Inter',system-ui,sans-serif;" onclick="filterTransactions('cancelled')">Cancelados</button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table id="tx-table" class="table-minimal">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Descricao</th>
                        <th style="text-align:right;">Valor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" style="padding:2rem;text-align:center;color:#666;font-size:0.8125rem;">Carregando...</td></tr>
                </tbody>
            </table>
        </div>

        <div id="tx-table-empty" class="hidden" style="padding:2rem;text-align:center;">
            <div class="empty-icon" style="margin:0 auto 0.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <p id="tx-empty-message" style="font-size:0.8125rem;color:#666;">Nenhuma transacao encontrada</p>
            <a href="/pix" style="display:inline-block;margin-top:0.5rem;font-size:0.8125rem;color:#fff;text-decoration:none;">Gerar PIX</a>
        </div>

        <div style="padding:0.625rem 1rem;border-top:1px solid #141414;display:flex;align-items:center;justify-content:space-between;">
            <p id="tx-page-info" style="font-size:0.75rem;color:#666;margin:0;">Pagina 1 de 1</p>
            <div style="display:flex;gap:0.5rem;">
                <button id="tx-prev" onclick="prevTxPage()" disabled class="btn-secondary" style="font-size:0.75rem;">Anterior</button>
                <button id="tx-next" onclick="nextTxPage()" disabled class="btn-secondary" style="font-size:0.75rem;">Proxima</button>
            </div>
        </div>
    </div>

</div>

<div id="tx-detail-modal" class="hidden" style="position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.7);" onclick="closeTxDetail()" class="modal-backdrop"></div>
    <div class="card animate-fade-in" style="position:relative;max-width:420px;width:100%;z-index:10;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 style="font-size:1rem;font-weight:600;color:#fff;margin:0;font-family:'Space Grotesk',sans-serif;">Detalhes da Transacao</h3>
            <button onclick="closeTxDetail()" style="background:none;border:none;color:#666;cursor:pointer;padding:4px;font-size:1.25rem;line-height:1;">&times;</button>
        </div>
        <div style="display:flex;flex-direction:column;gap:0.75rem;">
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:0.8125rem;color:#888;">ID</span>
                <span id="tx-detail-id" style="font-size:0.8125rem;color:#fff;font-family:'JetBrains Mono',monospace;">#000000</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:0.8125rem;color:#888;">Valor</span>
                <span id="tx-detail-amount" style="font-size:0.8125rem;color:#fff;font-family:'JetBrains Mono',monospace;">R$ 0,00</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:0.8125rem;color:#888;">Status</span>
                <span id="tx-detail-badge"></span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:0.8125rem;color:#888;">Descricao</span>
                <span id="tx-detail-description" style="font-size:0.8125rem;color:#ccc;">-</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:0.8125rem;color:#888;">Data</span>
                <span id="tx-detail-date" style="font-size:0.8125rem;color:#ccc;">-</span>
            </div>
            <div id="tx-detail-code-block" class="hidden">
                <p style="font-size:0.6875rem;color:#666;margin:0 0 4px 0;">Codigo PIX</p>
                <div style="display:flex;gap:0.5rem;">
                    <code id="tx-detail-code" style="flex:1;padding:0.5rem;background:#000;border:1px solid #141414;border-radius:8px;font-size:0.75rem;font-family:'JetBrains Mono',monospace;color:#888;word-break:break-all;"></code>
                    <button onclick="copyToClipboard('tx-detail-code')" class="btn-secondary" style="font-size:0.75rem;">Copiar</button>
                </div>
            </div>
        </div>
    </div>
</div>
