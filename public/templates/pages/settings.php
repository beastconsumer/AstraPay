<!-- ================================================================
     SETTINGS PAGE (Auth Required)
     ================================================================ -->
<div class="max-w-2xl mx-auto">

    <!-- Tabs -->
    <div class="flex items-center gap-6 border-b border-zinc-800 mb-6 overflow-x-auto">
        <button data-settings-tab="profile" class="settings-tab tab-active text-zinc-100 text-sm font-medium pb-3 -mb-[1px] transition-fast whitespace-nowrap" onclick="switchSettingsTab('profile')">Perfil</button>
        <button data-settings-tab="pixkey" class="settings-tab text-zinc-400 text-sm font-medium pb-3 -mb-[1px] transition-fast whitespace-nowrap" onclick="switchSettingsTab('pixkey')">Chave PIX</button>
        <button data-settings-tab="security" class="settings-tab text-zinc-400 text-sm font-medium pb-3 -mb-[1px] transition-fast whitespace-nowrap" onclick="switchSettingsTab('security')">Seguranca</button>
    </div>

    <!-- Tab: Perfil -->
    <div id="settings-panel-profile" class="settings-panel">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 lg:p-8 animate-slide-up">
            <div class="flex items-center gap-4 mb-6">
                <div id="settings-avatar" class="w-14 h-14 rounded-full bg-violet-500/20 text-violet-400 flex items-center justify-center text-xl font-semibold">
                    <span id="settings-avatar-letter">U</span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-zinc-100" id="settings-name-display">Usuario</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="settings-tier-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-800 text-zinc-400 border border-zinc-700">Novo</span>
                        <span id="settings-email-verified" class="hidden inline-flex items-center gap-1 text-xs text-emerald-400">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Verificado
                        </span>
                    </div>
                </div>
            </div>

            <form onsubmit="event.preventDefault(); saveProfile();" class="space-y-4">
                <div>
                    <label for="settings-name" class="block text-sm font-medium text-zinc-300 mb-1.5">Nome completo</label>
                    <input type="text" id="settings-name" name="name" required
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-300 mb-1.5">Email</label>
                    <div class="flex items-center gap-2">
                        <p id="settings-email-display" class="flex-1 text-sm text-zinc-100 py-2.5">email@exemplo.com</p>
                    </div>
                    <p class="text-xs text-zinc-600 mt-1">O email nao pode ser alterado.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-300 mb-1.5">CPF</label>
                    <p id="settings-cpf-display" class="text-sm text-zinc-100 py-2.5 font-mono">Nao informado</p>
                </div>

                <div>
                    <label for="settings-phone" class="block text-sm font-medium text-zinc-300 mb-1.5">Celular</label>
                    <input type="text" id="settings-phone" name="phone" maxlength="15"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm font-mono"
                           placeholder="(00) 00000-0000">
                </div>

                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 rounded-md text-sm font-medium bg-violet-500 hover:bg-violet-400 text-white transition-fast">
                    Salvar alteracoes
                </button>
            </form>
        </div>
    </div>

    <!-- Tab: Chave PIX -->
    <div id="settings-panel-pixkey" class="settings-panel hidden">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 lg:p-8 animate-slide-up">
            <div class="bg-violet-500/10 border border-violet-500/20 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-violet-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <div>
                        <p class="text-sm text-violet-300 font-medium mb-1">Destino de Saques</p>
                        <p class="text-xs text-violet-400/70">Seu dinheiro sera enviado para esta chave PIX automaticamente apos cada pagamento confirmado.</p>
                    </div>
                </div>
            </div>

            <form onsubmit="event.preventDefault(); savePixKey();" class="space-y-4">
                <div>
                    <label for="settings-pix-type" class="block text-sm font-medium text-zinc-300 mb-1.5">Tipo de Chave</label>
                    <select id="settings-pix-type" name="pix_key_type"
                            class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 input-focus transition-normal text-sm">
                        <option value="cpf">CPF</option>
                        <option value="cnpj">CNPJ</option>
                        <option value="email">Email</option>
                        <option value="phone">Telefone</option>
                        <option value="random">Aleatoria (EVP)</option>
                    </select>
                </div>

                <div>
                    <label for="settings-pix-key" class="block text-sm font-medium text-zinc-300 mb-1.5">Chave PIX</label>
                    <input type="text" id="settings-pix-key" name="pix_key" required
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm font-mono"
                           placeholder="Sua chave PIX">
                    <p class="text-xs text-zinc-600 mt-1">Alterar a chave PIX afeta saques futuros imediatamente.</p>
                </div>

                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 rounded-md text-sm font-medium bg-violet-500 hover:bg-violet-400 text-white transition-fast">
                    Salvar chave PIX
                </button>
            </form>
        </div>
    </div>

    <!-- Tab: Seguranca -->
    <div id="settings-panel-security" class="settings-panel hidden">
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 lg:p-8 animate-slide-up">
            <h3 class="text-lg font-semibold text-zinc-100 mb-6">Alterar Senha</h3>

            <form onsubmit="event.preventDefault(); changePassword();" class="space-y-4">
                <div>
                    <label for="settings-pw-current" class="block text-sm font-medium text-zinc-300 mb-1.5">Senha Atual</label>
                    <input type="password" id="settings-pw-current" name="current_password" required
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Sua senha atual">
                </div>

                <div>
                    <label for="settings-pw-new" class="block text-sm font-medium text-zinc-300 mb-1.5">Nova Senha</label>
                    <input type="password" id="settings-pw-new" name="password" required minlength="8"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Minimo 8 caracteres">
                </div>

                <div>
                    <label for="settings-pw-confirm" class="block text-sm font-medium text-zinc-300 mb-1.5">Confirmar Nova Senha</label>
                    <input type="password" id="settings-pw-confirm" name="password_confirmation" required minlength="8"
                           class="w-full bg-zinc-900 border border-zinc-800 rounded-md px-3 py-2.5 text-zinc-100 placeholder:text-zinc-500 input-focus transition-normal text-sm"
                           placeholder="Repita a nova senha">
                </div>

                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 rounded-md text-sm font-medium bg-zinc-800 hover:bg-zinc-700 text-zinc-100 border border-zinc-700 transition-fast">
                    Alterar Senha
                </button>
            </form>
        </div>

        <!-- Logout -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6 mt-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-300">Sair da conta</p>
                    <p class="text-xs text-zinc-500 mt-0.5">Voce precisara fazer login novamente.</p>
                </div>
                <button onclick="AstraPay.auth.logout()"
                        class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 transition-fast">
                    Sair
                </button>
            </div>
        </div>
    </div>

</div>
