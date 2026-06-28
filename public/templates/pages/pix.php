<div style="max-width:540px;margin:0 auto;">

    <div id="pix-form-card">
        <div class="card">
            <h2 style="font-size:1rem;font-weight:600;color:#ffffff;margin:0 0 1.25rem 0;font-family:'Space Grotesk',sans-serif;">Nova Cobranca PIX</h2>

            <div id="pix-error" class="hidden banner banner-error" style="margin-bottom:1rem;"></div>

            <form id="pix-form" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label for="pix-amount" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Valor (R$)</label>
                    <input type="text" id="pix-amount" name="valor" required autofocus
                           style="width:100%;padding:0.875rem 0.75rem;background:#000;border:1px solid #141414;border-radius:8px;color:#fff;font-size:1.5rem;font-family:'JetBrains Mono',monospace;font-variant-numeric:tabular-nums;outline:none;box-sizing:border-box;"
                           placeholder="0,00">
                </div>

                <div>
                    <label for="pix-description" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Descricao <span style="color:#333;">(opcional)</span></label>
                    <input type="text" id="pix-description" name="descricao" maxlength="255"
                           style="width:100%;padding:0.75rem 0.75rem;background:#000;border:1px solid #141414;border-radius:8px;color:#fff;font-size:0.875rem;outline:none;font-family:'Inter',system-ui,sans-serif;box-sizing:border-box;"
                           placeholder="Ex: Pagamento do pedido #1234">
                </div>

                <button type="submit" id="pix-submit" class="astra-btn" style="width:100%;">
                    <span id="pix-submit-text">Gerar PIX</span>
                    <span id="pix-spinner" class="hidden spinner"></span>
                </button>
            </form>
        </div>
    </div>

    <div id="pix-result" class="hidden">
        <div class="card animate-fade-in">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <h2 style="font-size:1rem;font-weight:600;color:#ffffff;margin:0;font-family:'Space Grotesk',sans-serif;">PIX Gerado</h2>
                <span id="pix-status-badge" class="status-badge pending">Aguardando</span>
            </div>

            <div id="pix-result-qr-container" style="text-align:center;margin-bottom:1.25rem;">
                <div class="qr-frame" style="padding:1rem;">
                    <img id="pix-result-qr" src="" alt="QR Code PIX" style="width:180px;height:180px;display:block;">
                </div>
            </div>

            <div style="text-align:center;margin-bottom:1.25rem;">
                <p style="font-size:0.6875rem;color:#666;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.25rem;">Valor</p>
                <p id="pix-result-amount" style="font-size:1.75rem;font-weight:600;color:#ffffff;font-family:'JetBrains Mono',monospace;font-variant-numeric:tabular-nums;margin:0;">R$ 0,00</p>
                <p style="font-size:0.75rem;color:#555;margin-top:0.25rem;">ID: <span id="pix-result-id" style="font-family:'JetBrains Mono',monospace;">#000000</span></p>
            </div>

            <div style="margin-bottom:1.25rem;">
                <p style="font-size:0.6875rem;color:#666;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.375rem;">Codigo Copia-e-Cola</p>
                <div style="display:flex;gap:0.5rem;">
                    <code id="pix-result-code" style="flex:1;padding:0.625rem;background:#000;border:1px solid #141414;border-radius:8px;font-size:0.75rem;font-family:'JetBrains Mono',monospace;color:#888;word-break:break-all;"></code>
                    <button onclick="copyToClipboard('pix-result-code')" class="btn-secondary" style="white-space:nowrap;">Copiar</button>
                </div>
            </div>

            <div id="pix-polling" style="text-align:center;padding:1rem 0;">
                <div class="spinner spinner-lg" style="margin:0 auto 0.75rem;"></div>
                <p style="font-size:0.8125rem;color:#888;">Aguardando pagamento...</p>
            </div>

            <div id="pix-success" class="hidden" style="text-align:center;padding:1.5rem 0;">
                <div style="width:48px;height:48px;border-radius:50%;border:1px solid #333;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 style="font-size:1.125rem;font-weight:600;color:#fff;margin:0 0 0.25rem 0;">Pago</h3>
                <p style="font-size:0.8125rem;color:#888;margin:0;">Pagamento recebido com sucesso.</p>
            </div>

            <button onclick="newPixForm()" class="btn-secondary" style="width:100%;margin-top:0.5rem;height:48px;">Gerar Novo PIX</button>
        </div>
    </div>

</div>
