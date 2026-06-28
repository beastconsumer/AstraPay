<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
    <div style="width:100%;max-width:360px;text-align:center;">
        <div style="margin-bottom:2rem;">
            <a href="/" style="font-size:1.125rem;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:-0.01em;">AstraPay</a>
        </div>

        <div id="verify-loading">
            <div class="spinner spinner-lg" style="margin:0 auto 0.75rem;"></div>
            <p style="font-size:0.8125rem;color:#888;">Verificando seu email...</p>
        </div>

        <div id="verify-success" class="hidden">
            <div style="width:48px;height:48px;border-radius:50%;border:1px solid #333;display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h2 style="font-size:1.125rem;font-weight:500;color:#fff;margin-bottom:0.25rem;">Email verificado</h2>
            <p style="font-size:0.8125rem;color:#888;">Redirecionando para o dashboard...</p>
        </div>

        <div id="verify-error" class="hidden">
            <div style="width:48px;height:48px;border-radius:50%;border:1px solid rgba(239,68,68,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <h2 style="font-size:1.125rem;font-weight:500;color:#fff;margin-bottom:0.25rem;">Erro na verificacao</h2>
            <p style="font-size:0.8125rem;color:#ef4444;margin-bottom:1rem;" id="verify-error-msg">Token invalido ou expirado.</p>
            <a href="/login" class="btn-secondary" style="text-decoration:none;">Ir para login</a>
        </div>
    </div>
</div>
