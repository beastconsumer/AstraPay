<div style="max-width:620px;margin:0 auto;">

    <div style="display:flex;gap:1.5rem;border-bottom:1px solid #141414;margin-bottom:1.5rem;overflow-x:auto;">
        <button data-settings-tab="profile" class="settings-tab tab-active" style="font-size:0.8125rem;font-weight:500;color:#fff;background:none;border:none;border-bottom:2px solid #ffffff;padding-bottom:0.625rem;cursor:pointer;font-family:'Inter',system-ui,sans-serif;margin-bottom:-1px;" onclick="switchSettingsTab('profile')">Perfil</button>
        <button data-settings-tab="pixkey" class="settings-tab" style="font-size:0.8125rem;font-weight:500;color:#666;background:none;border:none;padding-bottom:0.625rem;cursor:pointer;font-family:'Inter',system-ui,sans-serif;" onclick="switchSettingsTab('pixkey')">Chave PIX</button>
        <button data-settings-tab="security" class="settings-tab" style="font-size:0.8125rem;font-weight:500;color:#666;background:none;border:none;padding-bottom:0.625rem;cursor:pointer;font-family:'Inter',system-ui,sans-serif;" onclick="switchSettingsTab('security')">Seguranca</button>
    </div>

    <div id="settings-panel-profile" class="settings-panel">
        <div class="card animate-fade-in">
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem;">
                <div style="width:48px;height:48px;border-radius:8px;background:#0a0a0a;border:1px solid #141414;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:600;color:#fff;" id="settings-avatar-letter">U</div>
                <div>
                    <h3 style="font-size:1rem;font-weight:600;color:#fff;margin:0;font-family:'Space Grotesk',sans-serif;" id="settings-name-display">Usuario</h3>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:2px;">
                        <span id="settings-tier-badge" class="badge-tier">Novo</span>
                        <span id="settings-email-verified" class="hidden" style="font-size:0.6875rem;color:#22c55e;">Verificado</span>
                    </div>
                </div>
            </div>

            <form onsubmit="event.preventDefault(); saveProfile();" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label for="settings-name" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Nome completo</label>
                    <input type="text" id="settings-name" name="name" required class="astra-input">
                </div>
                <div>
                    <label style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Email</label>
                    <p id="settings-email-display" style="font-size:0.875rem;color:#fff;margin:0;">email@exemplo.com</p>
                </div>
                <div>
                    <label style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">CPF</label>
                    <p id="settings-cpf-display" style="font-size:0.875rem;color:#fff;font-family:'JetBrains Mono',monospace;margin:0;">Nao informado</p>
                </div>
                <div>
                    <label for="settings-phone" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Celular</label>
                    <input type="text" id="settings-phone" name="phone" maxlength="15" class="astra-input astra-input-mono" placeholder="(00) 00000-0000">
                </div>
                <button type="submit" class="astra-btn">Salvar alteracoes</button>
            </form>
        </div>
    </div>

    <div id="settings-panel-pixkey" class="settings-panel hidden">
        <div class="card animate-fade-in">
            <p style="font-size:0.8125rem;color:#fff;margin:0 0 0.75rem 0;">Seu dinheiro sera enviado para esta chave PIX automaticamente apos cada pagamento confirmado.</p>

            <form onsubmit="event.preventDefault(); savePixKey();" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label for="settings-pix-type" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Tipo de Chave</label>
                    <select id="settings-pix-type" name="pix_key_type"
                            style="width:100%;padding:0.75rem 0.875rem;background:#000;border:1px solid #141414;border-radius:8px;color:#fff;font-size:0.875rem;outline:none;font-family:'Inter',system-ui,sans-serif;box-sizing:border-box;">
                        <option value="cpf">CPF</option>
                        <option value="cnpj">CNPJ</option>
                        <option value="email">Email</option>
                        <option value="phone">Telefone</option>
                        <option value="random">Aleatoria (EVP)</option>
                    </select>
                </div>
                <div>
                    <label for="settings-pix-key" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Chave PIX</label>
                    <input type="text" id="settings-pix-key" name="pix_key" required class="astra-input astra-input-mono" placeholder="Sua chave PIX">
                </div>
                <button type="submit" class="astra-btn">Salvar chave PIX</button>
            </form>
        </div>
    </div>

    <div id="settings-panel-security" class="settings-panel hidden">
        <div class="card animate-fade-in">
            <h3 style="font-size:1rem;font-weight:600;color:#fff;margin:0 0 1rem 0;font-family:'Space Grotesk',sans-serif;">Alterar Senha</h3>
            <form onsubmit="event.preventDefault(); changePassword();" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label for="settings-pw-current" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Senha Atual</label>
                    <input type="password" id="settings-pw-current" name="current_password" required class="astra-input" placeholder="Sua senha atual">
                </div>
                <div>
                    <label for="settings-pw-new" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Nova Senha</label>
                    <input type="password" id="settings-pw-new" name="password" required minlength="8" class="astra-input" placeholder="Minimo 8 caracteres">
                </div>
                <div>
                    <label for="settings-pw-confirm" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Confirmar Nova Senha</label>
                    <input type="password" id="settings-pw-confirm" name="password_confirmation" required minlength="8" class="astra-input" placeholder="Repita a nova senha">
                </div>
                <button type="submit" class="astra-btn" style="align-self:flex-start;">Alterar Senha</button>
            </form>
        </div>

        <div class="card" style="margin-top:0.75rem;display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;">
            <p style="font-size:0.8125rem;color:#888;margin:0;">Sair da conta</p>
            <button onclick="AstraPay.auth.logout()" class="btn-danger">Sair</button>
        </div>
    </div>

</div>
