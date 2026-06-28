<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'AstraPay'; ?></title>
    <link rel="icon" type="image/png" href="/assets/logobola.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              heading: ['Space Grotesk', 'sans-serif'],
              sans: ['Inter', 'system-ui', 'sans-serif'],
              mono: ['JetBrains Mono', 'monospace'],
            },
          }
        }
      }
    </script>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-black text-white font-sans antialiased" data-page="<?php echo $page; ?>" style="background:#000000;">

<?php if ($layout === 'app'): ?>
    <header style="position:sticky;top:0;z-index:20;background:rgba(0,0,0,0.85);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border-bottom:1px solid #141414;">
        <div style="max-width:1200px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:52px;">
            <div style="display:flex;align-items:center;gap:2rem;">
                <a href="/dashboard" style="display:flex;align-items:center;text-decoration:none;">
                    <img src="/assets/logoescrita.png" alt="AstraPay" style="height:28px;width:auto;">
                </a>
                <nav style="display:flex;gap:1.5rem;">
                    <a href="/dashboard" style="font-size:0.8125rem;color:<?php echo $page==='dashboard'?'#ffffff':'#666'; ?>;text-decoration:none;transition:color 0.15s;font-weight:500;">Dashboard</a>
                    <a href="/pix" style="font-size:0.8125rem;color:<?php echo $page==='pix'?'#ffffff':'#666'; ?>;text-decoration:none;transition:color 0.15s;font-weight:500;">PIX</a>
                    <a href="/transactions" style="font-size:0.8125rem;color:<?php echo $page==='transactions'?'#ffffff':'#666'; ?>;text-decoration:none;transition:color 0.15s;font-weight:500;">Transacoes</a>
                    <a href="/settings" style="font-size:0.8125rem;color:<?php echo $page==='settings'?'#ffffff':'#666'; ?>;text-decoration:none;transition:color 0.15s;font-weight:500;">Configuracoes</a>
                </nav>
            </div>
            <div style="position:relative;">
                <button id="user-dropdown-btn" onclick="toggleUserDropdown()" style="display:flex;align-items:center;gap:0.5rem;padding:0.25rem;background:none;border:none;cursor:pointer;color:#888;font-family:'Inter',system-ui,sans-serif;font-size:0.8125rem;">
                    <span id="dash-user-avatar" style="width:28px;height:28px;border-radius:6px;background:#0a0a0a;border:1px solid #141414;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:500;color:#fff;">U</span>
                    <span id="dash-user-name" style="color:#fff;font-size:0.8125rem;">Usuario</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div id="user-dropdown" class="hidden" style="position:absolute;right:0;top:calc(100% + 4px);width:192px;background:#0a0a0a;border:1px solid #141414;border-radius:12px;z-index:50;overflow:hidden;">
                    <div style="padding:0.625rem 0.75rem;border-bottom:1px solid #141414;">
                        <p id="dash-user-email" style="font-size:0.8125rem;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">email</p>
                        <span id="dash-user-tier" style="display:inline-block;margin-top:4px;padding:1px 6px;border-radius:9999px;font-size:0.625rem;color:#888;border:1px solid #141414;">Novo</span>
                    </div>
                    <a href="/settings" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;font-size:0.8125rem;color:#888;text-decoration:none;transition:color 0.15s;">Configuracoes</a>
                    <button onclick="AstraPay.auth.logout()" style="width:100%;display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;font-size:0.8125rem;color:#888;background:none;border:none;cursor:pointer;text-align:left;font-family:'Inter',system-ui,sans-serif;transition:color 0.15s;">Sair</button>
                </div>
            </div>
        </div>
    </header>
    <main style="max-width:1200px;margin:0 auto;padding:1.5rem;">
        <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
    </main>

<?php elseif ($layout === 'auth'): ?>
    <main style="min-height:100vh;">
        <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
    </main>

<?php else: ?>
    <main style="min-height:100vh;">
        <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
    </main>
<?php endif; ?>

<div id="toast-container" style="position:fixed;bottom:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;pointer-events:none;"></div>

<script src="/assets/js/app.js"></script>
</body>
</html>
