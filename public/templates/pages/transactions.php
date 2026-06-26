<!-- ================================================================
     TRANSACTIONS PAGE (Auth Required)
     ================================================================ -->
<div id="tx-table-container" data-page="1" data-status="all">

    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
        <!-- Header + Filter Tabs -->
        <div class="px-5 py-4 border-b border-zinc-800">
            <div class="flex items-center gap-6 overflow-x-auto">
                <button data-status="all" class="tx-filter-tab tab-active text-zinc-100 text-sm font-medium pb-2 -mb-[1px] transition-fast whitespace-nowrap" onclick="filterTransactions('all')">Todas</button>
                <button data-status="pending" class="tx-filter-tab text-zinc-400 text-sm font-medium pb-2 -mb-[1px] transition-fast whitespace-nowrap" onclick="filterTransactions('pending')">Pendentes</button>
                <button data-status="confirmed" class="tx-filter-tab text-zinc-400 text-sm font-medium pb-2 -mb-[1px] transition-fast whitespace-nowrap" onclick="filterTransactions('confirmed')">Confirmados</button>
                <button data-status="cancelled" class="tx-filter-tab text-zinc-400 text-sm font-medium pb-2 -mb-[1px] transition-fast whitespace-nowrap" onclick="filterTransactions('cancelled')">Cancelados</button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto custom-scrollbar">
            <table id="tx-table" class="w-full">
                <thead>
                    <tr class="bg-zinc-900/50 border-b border-zinc-800">
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Descricao</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wider">Valor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">
                            <div class="p-8 space-y-3">
                                <div class="skeleton skeleton-row"></div>
                                <div class="skeleton skeleton-row"></div>
                                <div class="skeleton skeleton-row"></div>
                                <div class="skeleton skeleton-row"></div>
                                <div class="skeleton skeleton-row"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="tx-table-empty" class="hidden p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-zinc-800 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </div>
            <p class="text-sm text-zinc-500">Nenhuma transacao encontrada</p>
            <a href="/pix" class="inline-block mt-2 text-sm text-violet-400 hover:text-violet-300 transition-fast">Gerar PIX</a>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-3 border-t border-zinc-800 flex items-center justify-between">
            <p id="tx-page-info" class="text-xs text-zinc-500">Pagina 1 de 1</p>
            <div class="flex items-center gap-2">
                <button id="tx-prev" onclick="prevTxPage()" disabled
                        class="px-3 py-1.5 rounded-md text-xs font-medium border border-zinc-700 text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100 transition-fast disabled:opacity-40 disabled:cursor-not-allowed">
                    Anterior
                </button>
                <button id="tx-next" onclick="nextTxPage()" disabled
                        class="px-3 py-1.5 rounded-md text-xs font-medium border border-zinc-700 text-zinc-400 hover:bg-zinc-800 hover:text-zinc-100 transition-fast disabled:opacity-40 disabled:cursor-not-allowed">
                    Proxima
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Transaction Detail Modal -->
<div id="tx-detail-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeTxDetail()"></div>
    <div class="relative bg-zinc-900 border border-zinc-800 rounded-xl max-w-md w-full p-6 shadow-lg animate-slide-up z-10">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-semibold text-zinc-100">Detalhes da Transacao</h3>
            <button onclick="closeTxDetail()" class="p-1 rounded-md text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800 transition-fast">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-sm text-zinc-400">ID</span>
                <span id="tx-detail-id" class="text-sm font-mono text-zinc-100">#000000</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-zinc-400">Valor</span>
                <span id="tx-detail-amount" class="text-sm font-mono text-zinc-100 tabular-nums">R$ 0,00</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-zinc-400">Status</span>
                <span id="tx-detail-badge"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-zinc-400">Descricao</span>
                <span id="tx-detail-description" class="text-sm text-zinc-300">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-zinc-400">Data</span>
                <span id="tx-detail-date" class="text-sm text-zinc-300">-</span>
            </div>

            <div id="tx-detail-code-block" class="hidden">
                <p class="text-xs text-zinc-500 mb-1.5">Codigo PIX</p>
                <div class="flex items-stretch gap-2">
                    <code id="tx-detail-code" class="flex-1 bg-zinc-950 border border-zinc-800 rounded-md px-3 py-2 text-xs font-mono text-zinc-300 break-all"></code>
                    <button onclick="copyToClipboard('tx-detail-code')" class="shrink-0 inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border border-zinc-700 transition-fast">
                        Copiar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
