<!-- LANDING PAGE: Video Hero + Glassmorphism Feature Cards -->
<video autoplay muted loop playsinline style="position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;filter:brightness(0.3);">
    <source src="/assets/hero-bg.mp4" type="video/mp4">
</video>
<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:-1;"></div>

<header style="position:fixed;top:0;left:0;right:0;z-index:50;background:rgba(0,0,0,0.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid #141414;">
    <div style="max-width:1200px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:56px;">
        <a href="/" style="display:flex;align-items:center;text-decoration:none;">
            <img src="/assets/logoescrita.png" alt="AstraPay" style="height:30px;width:auto;">
        </a>
        <div style="display:flex;align-items:center;gap:1rem;">
            <a href="/login" style="font-size:0.8125rem;color:#888;text-decoration:none;transition:color 0.15s;font-weight:500;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#888'">Entrar</a>
            <a href="/register" style="display:inline-flex;align-items:center;padding:0.5rem 1rem;background:#ffffff;color:#000000;border-radius:8px;font-size:0.8125rem;font-weight:500;text-decoration:none;transition:opacity 0.15s;height:36px;">Criar Conta</a>
        </div>
    </div>
</header>

<section style="padding:10rem 1.5rem 6rem;text-align:center;max-width:800px;margin:0 auto;">
    <div style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.375rem 0.875rem;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:9999px;font-size:0.75rem;color:#888;margin-bottom:2rem;">
        <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
        Pagamentos PIX instantaneos
    </div>

    <h1 style="font-size:3.5rem;font-weight:700;color:#ffffff;letter-spacing:-0.03em;line-height:1.1;margin-bottom:1.5rem;font-family:'Space Grotesk',sans-serif;">Infraestrutura de<br>pagamentos PIX</h1>

    <p style="font-size:1.125rem;color:#888;margin-bottom:2.5rem;line-height:1.6;max-width:520px;margin-left:auto;margin-right:auto;">Crie cobrancas PIX instantaneas. Receba de qualquer banco. Sem maquininha, sem taxas escondidas.</p>

    <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap;">
        <a href="/register" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0 1.5rem;background:#ffffff;color:#000000;border-radius:8px;font-size:0.9375rem;font-weight:500;text-decoration:none;transition:opacity 0.15s;height:48px;line-height:48px;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
            Comecar agora
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="/api-docs" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0 1.5rem;background:transparent;color:#ffffff;border:1px solid rgba(255,255,255,0.2);border-radius:8px;font-size:0.9375rem;font-weight:500;text-decoration:none;transition:all 0.15s;height:48px;line-height:48px;" onmouseover="this.style.borderColor='rgba(255,255,255,0.5)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)'">
            Documentacao API
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </a>
    </div>
</section>

<section style="padding:4rem 1.5rem;max-width:1000px;margin:0 auto;">
    <p style="text-align:center;font-size:0.75rem;color:#666;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:2.5rem;">Por que AstraPay</p>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;">
        <div style="text-align:center;padding:1.5rem;">
            <div class="glass-icon" style="width:80px;height:80px;border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.5"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </div>
            <h3 style="font-size:1rem;font-weight:600;color:#ffffff;margin-bottom:0.5rem;font-family:'Space Grotesk',sans-serif;">0% taxa</h3>
            <p style="font-size:0.875rem;color:#888;line-height:1.5;">Voce recebe o valor integral de cada pagamento PIX.</p>
        </div>
        <div style="text-align:center;padding:1.5rem;">
            <div class="glass-icon" style="width:80px;height:80px;border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <h3 style="font-size:1rem;font-weight:600;color:#ffffff;margin-bottom:0.5rem;font-family:'Space Grotesk',sans-serif;">PIX instantaneo</h3>
            <p style="font-size:0.875rem;color:#888;line-height:1.5;">QR Code e copia-e-cola em segundos, compativel com todos os bancos.</p>
        </div>
        <div style="text-align:center;padding:1.5rem;">
            <div class="glass-icon" style="width:80px;height:80px;border-radius:20px;background:rgba(255,255,255,0.05);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.5"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
            <h3 style="font-size:1rem;font-weight:600;color:#ffffff;margin-bottom:0.5rem;font-family:'Space Grotesk',sans-serif;">API REST</h3>
            <p style="font-size:0.875rem;color:#888;line-height:1.5;">Documentacao completa. Integre PIX no seu sistema em minutos.</p>
        </div>
    </div>
</section>

<section style="padding:5rem 1.5rem;text-align:center;border-top:1px solid #141414;">
    <h2 style="font-size:2rem;font-weight:700;color:#ffffff;letter-spacing:-0.02em;margin-bottom:1rem;font-family:'Space Grotesk',sans-serif;">Pronto para comecar?</h2>
    <p style="font-size:1rem;color:#888;margin-bottom:1.5rem;">Crie sua conta gratuita e comece a receber PIX em segundos.</p>
    <a href="/register" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0 1.75rem;background:#ffffff;color:#000000;border-radius:8px;font-size:0.9375rem;font-weight:500;text-decoration:none;transition:opacity 0.15s;height:48px;line-height:48px;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
        Criar conta gratis
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
</section>

<footer style="border-top:1px solid #141414;padding:1.5rem;text-align:center;">
    <p style="font-size:0.75rem;color:#555;">&copy; 2026 AstraPay</p>
</footer>
