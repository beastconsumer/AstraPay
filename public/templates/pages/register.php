<!-- ================================================================
     REGISTER PAGE
     ================================================================ -->
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md animate-slide-up">
        <div class="text-center mb-8">
            <a href="/" class="text-2xl font-bold gradient-text">AstraPay</a>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-8">
            <h1 class="text-xl font-semibold text-zinc-100 mb-1">Criar Conta</h1>
            <p class="text-sm text-zinc-400 mb-6">Comece a receber PIX em minutos.</p>

            <div id="register-error" class="hidden bg-red-500/10 border border-red-500/20 rounded-lg p-3 mb-6">
                <p class="text-sm text-red-400"></p>
            </div>

            <form id="register-form" class="space-y-4">
                <div>
                    <label for="register-name" class="block text-sm font-medium text-zinc-300 mb-1.5">Nome completo</label>
                    <input type="text" id="register-name" name="name" required autocomplete="name"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Seu nome completo">
                </div>

                <div>
                    <label for="register-email" class="block text-sm font-medium text-zinc-300 mb-1.5">Email</label>
                    <input type="email" id="register-email" name="email" required autocomplete="email"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="seu@email.com">
                </div>

                <div>
                    <label for="register-cpf" class="block text-sm font-medium text-zinc-300 mb-1.5">CPF</label>
                    <input type="text" id="register-cpf" name="cpf" autocomplete="off" maxlength="14"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm font-mono"
                           placeholder="000.000.000-00">
                </div>

                <div>
                    <label for="register-phone" class="block text-sm font-medium text-zinc-300 mb-1.5">Celular <span class="text-zinc-600">(opcional)</span></label>
                    <input type="text" id="register-phone" name="phone" autocomplete="tel" maxlength="15"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm font-mono"
                           placeholder="(00) 00000-0000">
                </div>

                <div>
                    <label for="register-password" class="block text-sm font-medium text-zinc-300 mb-1.5">Senha</label>
                    <input type="password" id="register-password" name="password" required autocomplete="new-password" minlength="8"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Minimo 8 caracteres">
                </div>

                <div>
                    <label for="register-password-confirm" class="block text-sm font-medium text-zinc-300 mb-1.5">Confirmar Senha</label>
                    <input type="password" id="register-password-confirm" name="password_confirmation" required autocomplete="new-password" minlength="8"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Repita a senha">
                </div>

                <button type="submit" id="register-submit"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-md text-sm font-medium bg-violet-500 hover:bg-violet-400 text-white transition-fast disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="register-submit-text">Criar Conta</span>
                    <span id="register-spinner" class="hidden spinner ml-2"></span>
                </button>
            </form>

            <p class="text-xs text-zinc-600 text-center mt-4">
                Ao criar conta voce concorda com os termos de uso.
            </p>

            <p class="text-sm text-zinc-500 text-center mt-4">
                Ja tem conta?
                <a href="/login" class="text-violet-400 hover:text-violet-300 transition-fast font-medium">Entrar</a>
            </p>
        </div>
    </div>
</div>
