<!-- ================================================================
     VERIFY EMAIL PAGE
     ================================================================ -->
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md text-center animate-slide-up">
        <div class="mb-8">
            <a href="/" class="text-2xl font-bold gradient-text">AstraPay</a>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-8">

            <!-- Loading State -->
            <div id="verify-loading">
                <div class="spinner spinner-lg mx-auto mb-4"></div>
                <p class="text-zinc-400 text-sm">Verificando seu email...</p>
            </div>

            <!-- Success State -->
            <div id="verify-success" class="hidden">
                <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-500 check-circle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray: 100; stroke-dashoffset: 0;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-zinc-100 mb-2">Email verificado!</h2>
                <p class="text-sm text-zinc-400">Redirecionando para o dashboard...</p>
            </div>

            <!-- Error State -->
            <div id="verify-error" class="hidden">
                <div class="w-16 h-16 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-zinc-100 mb-2">Erro na verificacao</h2>
                <p class="text-sm text-red-400 mb-6" id="verify-error-msg">Token invalido ou expirado.</p>
                <a href="/login" class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-100 border border-zinc-700 transition-fast">
                    Ir para login
                </a>
            </div>

        </div>
    </div>
</div>
