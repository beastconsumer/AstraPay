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
            <p style="font-size:0.9375rem;color:#777;">Plataforma de pagamentos PIX</p>
        </div>

        <div class="astra-card" style="padding:2rem;">
            <div id="login-error" class="hidden" style="padding:0.625rem 0.75rem;margin-bottom:1.25rem;font-size:0.8125rem;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:8px;color:#ef4444;"></div>

            <form id="login-form" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label for="login-email" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Email</label>
                    <input type="email" id="login-email" name="email" required autocomplete="email" class="astra-input" placeholder="seu@email.com">
                </div>

                <div>
                    <label for="login-password" style="display:block;font-size:0.8125rem;color:#888;margin-bottom:0.375rem;font-weight:500;">Senha</label>
                    <input type="password" id="login-password" name="password" required autocomplete="current-password" class="astra-input" placeholder="Sua senha">
                </div>

                <button type="button" id="login-submit" class="astra-btn" style="width:100%;height:48px;margin-top:0.5rem;" onclick="handleLoginSubmit()">
                    <span id="login-submit-text">Entrar</span>
                    <span id="login-spinner" class="hidden spinner"></span>
                </button>
            </form>
        </div>

        <p style="text-align:center;margin-top:1.75rem;font-size:0.875rem;color:#666;">
            Nao tem conta?
            <a href="/register" style="color:#ffffff;text-decoration:none;font-weight:500;transition:opacity 0.15s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Criar conta</a>
        </p>
    </div>

<script>
function handleLoginSubmit() {
    var btn = document.getElementById('login-submit');
    var bt = document.getElementById('login-submit-text');
    var sp = document.getElementById('login-spinner');
    var er = document.getElementById('login-error');

    btn.disabled = true;
    if (bt) bt.classList.add('hidden');
    if (sp) sp.classList.remove('hidden');
    if (er) er.classList.add('hidden');

    var email = (document.getElementById('login-email') || {}).value || '';
    var password = (document.getElementById('login-password') || {}).value || '';

    fetch('/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ email: email.trim(), password: password })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.data && data.data.token) {
            localStorage.setItem('astrapay_token', data.data.token);
            if (data.data.user) {
                localStorage.setItem('astrapay_user', JSON.stringify(data.data.user));
            }
            window.location.href = '/dashboard';
        } else {
            throw new Error(data.error || 'Credenciais invalidas');
        }
    })
    .catch(function(err) {
        if (er) {
            er.textContent = err.message || 'Erro ao realizar login';
            er.classList.remove('hidden');
        }
    })
    .finally(function() {
        btn.disabled = false;
        if (bt) bt.classList.remove('hidden');
        if (sp) sp.classList.add('hidden');
    });
}
</script>

</div>
