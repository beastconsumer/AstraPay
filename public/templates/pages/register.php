<video autoplay muted loop playsinline style="position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;filter:brightness(0.25) blur(8px);">
    <source src="/assets/login-bg.mp4" type="video/mp4">
</video>
<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(ellipse at 50% 50%, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.85) 100%);z-index:-1;"></div>

<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1.5rem;">
    <div style="width:100%;max-width:400px;">

        <div style="text-align:center;margin-bottom:2.5rem;">
            <a href="/" style="display:inline-block;text-decoration:none;">
                <img src="/assets/logoescrita.png" alt="AstraPay" style="height:36px;width:auto;margin-bottom:0.75rem;">
            </a>
            <p style="font-size:0.9375rem;color:#777;">Crie sua conta gratuita</p>
        </div>

        <div class="astra-card" style="padding:2rem;">
            <div id="register-error" class="hidden" style="padding:0.625rem 0.75rem;margin-bottom:1.25rem;font-size:0.8125rem;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:8px;color:#ef4444;"></div>

            <form id="register-form" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label for="register-name" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Nome completo</label>
                    <input type="text" id="register-name" name="name" required autocomplete="name" class="astra-input" placeholder="Seu nome completo">
                </div>

                <div>
                    <label for="register-email" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Email</label>
                    <input type="email" id="register-email" name="email" required autocomplete="email" class="astra-input" placeholder="seu@email.com">
                </div>

                <div>
                    <label for="register-cpf" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">CPF</label>
                    <input type="text" id="register-cpf" name="cpf" autocomplete="off" maxlength="14" class="astra-input" placeholder="000.000.000-00">
                </div>

                <div>
                    <label for="register-phone" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Celular</label>
                    <input type="text" id="register-phone" name="phone" autocomplete="tel" maxlength="15" class="astra-input" placeholder="(00) 00000-0000">
                </div>

                <div>
                    <label for="register-password" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Senha</label>
                    <input type="password" id="register-password" name="password" required autocomplete="new-password" minlength="8" class="astra-input" placeholder="Minimo 8 caracteres">
                </div>

                <div>
                    <label for="register-password-confirm" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Confirmar senha</label>
                    <input type="password" id="register-password-confirm" name="password_confirmation" required autocomplete="new-password" minlength="8" class="astra-input" placeholder="Repita a senha">
                </div>

                <button type="submit" id="register-submit" class="astra-btn" style="width:100%;height:48px;margin-top:0.5rem;">
                    <span id="register-submit-text">Criar Conta</span>
                    <span id="register-spinner" class="hidden spinner"></span>
                </button>
            </form>
        </div>

        <p style="text-align:center;margin-top:1.75rem;font-size:0.875rem;color:#666;">
            Ja tem conta?
            <a href="/login" style="color:#ffffff;text-decoration:none;font-weight:500;transition:opacity 0.15s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Entrar</a>
        </p>
    </div>
</div>
