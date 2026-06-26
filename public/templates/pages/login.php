<!-- ================================================================
     LOGIN PAGE
     ================================================================ -->
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md animate-slide-up">
        <div class="text-center mb-8">
            <a href="/" class="text-2xl font-bold gradient-text">AstraPay</a>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-8">
            <h1 class="text-xl font-semibold text-zinc-100 mb-1">Entrar</h1>
            <p class="text-sm text-zinc-400 mb-6">Acesse sua conta para continuar.</p>

            <div id="login-error" class="hidden bg-red-500/10 border border-red-500/20 rounded-lg p-3 mb-6">
                <p class="text-sm text-red-400"></p>
            </div>

            <form id="login-form" class="space-y-4">
                <div>
                    <label for="login-email" class="block text-sm font-medium text-zinc-300 mb-1.5">Email</label>
                    <input type="email" id="login-email" name="email" required autocomplete="email"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="seu@email.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="login-password" class="block text-sm font-medium text-zinc-300">Senha</label>
                        <a href="/forgot-password" class="text-xs text-violet-400 hover:text-violet-300 transition-fast">Esqueci minha senha</a>
                    </div>
                    <input type="password" id="login-password" name="password" required autocomplete="current-password"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Sua senha">
                </div>

                <button type="submit" id="login-submit"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md text-sm font-medium bg-violet-500 hover:bg-violet-400 text-white transition-fast disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="login-submit-text">Entrar</span>
                    <span id="login-spinner" class="hidden spinner ml-2"></span>
                </button>
            </form>

            <p class="text-sm text-zinc-500 text-center mt-6">
                Nao tem conta?
                <a href="/register" class="text-violet-400 hover:text-violet-300 transition-fast font-medium">Criar conta</a>
            </p>
        </div>
    </div>
</div>
