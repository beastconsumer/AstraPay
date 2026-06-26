<!-- ================================================================
     PIX GENERATOR PAGE (Auth Required)
     ================================================================ -->
<div class="max-w-2xl mx-auto">

    <!-- Step 1: Form -->
    <div id="pix-form-card">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 lg:p-8">
            <h2 class="text-lg font-semibold text-zinc-100 mb-6">Nova Cobranca PIX</h2>

            <div id="pix-error" class="hidden bg-red-500/10 border border-red-500/20 rounded-lg p-3 mb-6">
                <p class="text-sm text-red-400"></p>
            </div>

            <form id="pix-form" class="space-y-5">
                <div>
                    <label for="pix-amount" class="block text-sm font-medium text-zinc-300 mb-1.5">Valor (R$)</label>
                    <input type="text" id="pix-amount" name="valor" required autofocus
                           class="w-full bg-zinc-950 border border-zinc-800 rounded-md px-4 py-3 text-2xl font-mono text-zinc-100 placeholder:text-zinc-600 input-focus transition-normal"
                           placeholder="0,00">
                </div>

                <div>
                    <label for="pix-description" class="block text-sm font-medium text-zinc-300 mb-1.5">Descricao <span class="text-zinc-600">(opcional)</span></label>
                    <input type="text" id="pix-description" name="descricao" maxlength="255"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Ex: Pagamento do pedido #1234">
                </div>

                <button type="submit" id="pix-submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-medium bg-violet-500 hover:bg-violet-400 text-white transition-fast disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-violet-500/25">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    <span id="pix-submit-text">Gerar PIX</span>
                    <span id="pix-spinner" class="hidden spinner"></span>
                </button>
            </form>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 mt-4">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-violet-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <p class="text-xs text-zinc-500">O PIX gerado expira em 30 minutos. O pagador pode usar o QR Code ou o codigo copia-e-cola em qualquer banco.</p>
            </div>
        </div>
    </div>

    <!-- Step 2: Result -->
    <div id="pix-result" class="hidden">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 lg:p-8 animate-slide-up">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-zinc-100">PIX Gerado</h2>
                <span id="pix-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <span class="pulse-dot"></span>
                    Aguardando Pagamento
                </span>
            </div>

            <!-- QR Code -->
            <div id="pix-result-qr-container" class="text-center mb-6">
                <div class="qr-frame inline-block">
                    <img id="pix-result-qr" src="" alt="QR Code PIX" class="w-48 h-48">
                </div>
            </div>

            <!-- Amount -->
            <div class="text-center mb-6">
                <p class="text-xs text-zinc-500 uppercase tracking-wider mb-1">Valor</p>
                <p id="pix-result-amount" class="text-3xl font-mono font-semibold text-zinc-100 tabular-nums">R$ 0,00</p>
                <p class="text-xs text-zinc-600 mt-1">ID: <span id="pix-result-id" class="font-mono">#000000</span></p>
            </div>

            <!-- Copia-e-cola -->
            <div class="mb-6">
                <p class="text-xs text-zinc-500 uppercase tracking-wider mb-2">Codigo Copia-e-Cola</p>
                <div class="flex items-stretch gap-2">
                    <code id="pix-result-code" class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2.5 text-xs font-mono text-zinc-300 break-all"></code>
                    <button onclick="copyToClipboard('pix-result-code')" class="shrink-0 inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border border-zinc-700 transition-fast">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Copiar
                    </button>
                </div>
            </div>

            <!-- Polling -->
            <div id="pix-polling" class="text-center py-4">
                <div class="spinner spinner-lg mx-auto mb-3"></div>
                <p class="text-sm text-zinc-400">Aguardando pagamento...</p>
            </div>

            <!-- Success -->
            <div id="pix-success" class="hidden text-center py-6">
                <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-4 animate-fade-in">
                    <svg class="w-8 h-8 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation: scale-check 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-emerald-400 mb-1">Pago!</h3>
                <p class="text-sm text-zinc-400">Pagamento recebido com sucesso.</p>
            </div>

            <button onclick="newPixForm()" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-sm font-medium border border-zinc-700 text-zinc-300 hover:bg-zinc-800 hover:text-zinc-100 transition-fast">
                Gerar Novo PIX
            </button>
        </div>
    </div>

</div>
