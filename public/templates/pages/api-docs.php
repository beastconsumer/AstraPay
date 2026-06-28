<header style="position:fixed;top:0;left:0;right:0;z-index:50;background:rgba(0,0,0,0.85);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border-bottom:1px solid #1a1a1a;">
    <div style="max-width:1200px;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:52px;">
        <a href="/" style="font-size:1.125rem;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:-0.01em;">AstraPay</a>
        <div style="display:flex;align-items:center;gap:1rem;">
            <a href="/api-keys" style="font-size:0.8125rem;color:#888;text-decoration:none;transition:color 0.15s;">API Keys</a>
            <a href="/dashboard" style="font-size:0.8125rem;color:#888;text-decoration:none;transition:color 0.15s;">Dashboard</a>
            <a href="/login" style="display:inline-flex;align-items:center;padding:0.375rem 0.75rem;background:#ffffff;color:#000000;border-radius:6px;font-size:0.8125rem;font-weight:500;text-decoration:none;transition:opacity 0.15s;">Entrar</a>
        </div>
    </div>
</header>

<section style="padding:6rem 1.5rem 3rem;text-align:center;max-width:720px;margin:0 auto;">
    <div style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.25rem 0.75rem;background:#0a0a0a;border:1px solid #1a1a1a;border-radius:9999px;font-size:0.75rem;color:#888;margin-bottom:1.5rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        API v1
    </div>
    <h1 style="font-size:3rem;font-weight:600;color:#ffffff;letter-spacing:-0.02em;line-height:1.1;margin-bottom:1rem;font-family:'Inter',system-ui,sans-serif;">AstraPay API</h1>
    <p style="font-size:1rem;color:#888;margin-bottom:2rem;line-height:1.6;max-width:480px;margin-left:auto;margin-right:auto;">Integre PIX em minutos. Crie cobrancas, consulte status e gerencie transacoes com nossa API RESTful.</p>
    <div style="display:flex;align-items:center;justify-content:center;gap:0.75rem;">
        <a href="#quickstart" style="display:inline-flex;align-items:center;padding:0.625rem 1.25rem;background:#ffffff;color:#000000;border-radius:6px;font-size:0.8125rem;font-weight:500;text-decoration:none;">Quickstart</a>
        <a href="#endpoints" style="display:inline-flex;align-items:center;padding:0.625rem 1.25rem;background:transparent;color:#888;border:1px solid #1a1a1a;border-radius:6px;font-size:0.8125rem;font-weight:500;text-decoration:none;">Endpoints</a>
    </div>
</section>

<section style="border-top:1px solid #1a1a1a;padding:3rem 1.5rem;max-width:960px;margin:0 auto;">
    <div id="quickstart" style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem;font-weight:600;color:#ffffff;margin-bottom:0.5rem;">Quickstart</h2>
        <p style="font-size:0.8125rem;color:#888;margin-bottom:1.5rem;">Obtenha sua API key no painel e comece a integrar em segundos.</p>

        <div style="display:flex;flex-direction:column;gap:1rem;">
            <div>
                <h3 style="font-size:0.8125rem;font-weight:500;color:#888;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                    <span style="display:inline-block;padding:1px 5px;border-radius:4px;background:#0a0a0a;border:1px solid #1a1a1a;font-family:'JetBrains Mono',monospace;font-size:0.625rem;color:#fff;">curl</span>
                    Gerar um PIX
                </h3>
                <pre style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1rem;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#888;line-height:1.8;margin:0;"><code>curl -X POST https://astrapay.com.br/api/v1/pix \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: astrapay_SUA_CHAVE_AQUI" \
  -d '{
    "valor": 29.90,
    "descricao": "Pagamento pedido #1234"
  }'</code></pre>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <h3 style="font-size:0.8125rem;font-weight:500;color:#888;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                        <span style="display:inline-block;padding:1px 5px;border-radius:4px;background:#0a0a0a;border:1px solid #1a1a1a;font-family:'JetBrains Mono',monospace;font-size:0.625rem;color:#fff;">PHP</span>
                        cURL
                    </h3>
                    <pre style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1rem;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;color:#888;line-height:1.6;margin:0;"><code>$ch = curl_init('https://astrapay.com.br/api/v1/pix');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Api-Key: astrapay_SUA_CHAVE',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'valor' => 29.90,
        'descricao' => 'Pedido #1234',
    ]),
]);
$data = json_decode(curl_exec($ch), true);
curl_close($ch);</code></pre>
                </div>
                <div>
                    <h3 style="font-size:0.8125rem;font-weight:500;color:#888;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                        <span style="display:inline-block;padding:1px 5px;border-radius:4px;background:#0a0a0a;border:1px solid #1a1a1a;font-family:'JetBrains Mono',monospace;font-size:0.625rem;color:#fff;">Python</span>
                        requests
                    </h3>
                    <pre style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1rem;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;color:#888;line-height:1.6;margin:0;"><code>import requests

response = requests.post(
    "https://astrapay.com.br/api/v1/pix",
    headers={
        "Content-Type": "application/json",
        "X-Api-Key": "astrapay_SUA_CHAVE",
    },
    json={
        "valor": 29.90,
        "descricao": "Pedido #1234",
    },
)
data = response.json()
print(data)</code></pre>
                </div>
            </div>
        </div>
    </div>

    <div id="endpoints" style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem;font-weight:600;color:#ffffff;margin-bottom:0.5rem;">Endpoints</h2>
        <p style="font-size:0.8125rem;color:#888;margin-bottom:1.5rem;">Base URL: <code style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:4px;padding:1px 6px;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#fff;">https://astrapay.com.br/api/v1</code></p>

        <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid #1a1a1a;">
                        <th style="text-align:left;padding:0.625rem 1rem;font-size:0.6875rem;font-weight:500;color:#666;text-transform:uppercase;letter-spacing:0.05em;width:60px;">Metodo</th>
                        <th style="text-align:left;padding:0.625rem 1rem;font-size:0.6875rem;font-weight:500;color:#666;text-transform:uppercase;letter-spacing:0.05em;">URL</th>
                        <th style="text-align:left;padding:0.625rem 1rem;font-size:0.6875rem;font-weight:500;color:#666;text-transform:uppercase;letter-spacing:0.05em;width:80px;">Auth</th>
                        <th style="text-align:left;padding:0.625rem 1rem;font-size:0.6875rem;font-weight:500;color:#666;text-transform:uppercase;letter-spacing:0.05em;">Descricao</th>
                    </tr>
                </thead>
                <tbody style="font-size:0.8125rem;">
                    <tr style="border-bottom:1px solid #0a0a0a;">
                        <td style="padding:0.75rem 1rem;"><span style="color:#fff;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;font-weight:600;">POST</span></td>
                        <td style="padding:0.75rem 1rem;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#ccc;">/pix</td>
                        <td style="padding:0.75rem 1rem;"><span style="display:inline-block;padding:1px 6px;border-radius:9999px;font-size:0.625rem;background:#0a0a0a;border:1px solid #333;color:#888;">API Key</span></td>
                        <td style="padding:0.75rem 1rem;color:#888;">Criar nova cobranca PIX</td>
                    </tr>
                    <tr style="border-bottom:1px solid #0a0a0a;">
                        <td style="padding:0.75rem 1rem;"><span style="color:#888;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;font-weight:600;">GET</span></td>
                        <td style="padding:0.75rem 1rem;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#ccc;">/pix/{id}</td>
                        <td style="padding:0.75rem 1rem;"><span style="display:inline-block;padding:1px 6px;border-radius:9999px;font-size:0.625rem;background:#0a0a0a;border:1px solid #333;color:#888;">API Key</span></td>
                        <td style="padding:0.75rem 1rem;color:#888;">Consultar status de um PIX</td>
                    </tr>
                    <tr style="border-bottom:1px solid #0a0a0a;">
                        <td style="padding:0.75rem 1rem;"><span style="color:#888;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;font-weight:600;">GET</span></td>
                        <td style="padding:0.75rem 1rem;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#ccc;">/balance</td>
                        <td style="padding:0.75rem 1rem;"><span style="display:inline-block;padding:1px 6px;border-radius:9999px;font-size:0.625rem;background:#0a0a0a;border:1px solid #333;color:#888;">API Key</span></td>
                        <td style="padding:0.75rem 1rem;color:#888;">Consultar saldo do usuario</td>
                    </tr>
                    <tr>
                        <td style="padding:0.75rem 1rem;"><span style="color:#888;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;font-weight:600;">GET</span></td>
                        <td style="padding:0.75rem 1rem;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#ccc;">/transactions</td>
                        <td style="padding:0.75rem 1rem;"><span style="display:inline-block;padding:1px 6px;border-radius:9999px;font-size:0.625rem;background:#0a0a0a;border:1px solid #333;color:#888;">API Key</span></td>
                        <td style="padding:0.75rem 1rem;color:#888;">Listar transacoes (paginado)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem;font-weight:600;color:#ffffff;margin-bottom:1.5rem;">Detalhes dos Endpoints</h2>

        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                    <span style="color:#fff;font-family:'JetBrains Mono',monospace;font-size:0.75rem;font-weight:600;">POST</span>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:0.875rem;color:#fff;">/api/v1/pix</span>
                </div>
                <p style="font-size:0.8125rem;color:#888;margin-bottom:1rem;">Cria uma nova cobranca PIX. O pagador pode pagar usando QR Code ou copia-e-cola.</p>

                <h4 style="font-size:0.75rem;font-weight:500;color:#666;margin-bottom:0.5rem;">Parametros (JSON Body)</h4>
                <table style="width:100%;border-collapse:collapse;margin-bottom:1rem;font-size:0.75rem;">
                    <thead>
                        <tr style="border-bottom:1px solid #1a1a1a;">
                            <th style="text-align:left;padding:0.375rem 0.5rem;color:#666;font-weight:500;">Campo</th>
                            <th style="text-align:left;padding:0.375rem 0.5rem;color:#666;font-weight:500;">Tipo</th>
                            <th style="text-align:left;padding:0.375rem 0.5rem;color:#666;font-weight:500;">Obrigatorio</th>
                            <th style="text-align:left;padding:0.375rem 0.5rem;color:#666;font-weight:500;">Descricao</th>
                        </tr>
                    </thead>
                    <tbody style="color:#888;">
                        <tr><td style="padding:0.375rem 0.5rem;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;">valor</td><td style="padding:0.375rem 0.5rem;">float</td><td style="padding:0.375rem 0.5rem;color:#fff;">Sim</td><td style="padding:0.375rem 0.5rem;">Valor da cobranca em reais (ex: 29.90)</td></tr>
                        <tr><td style="padding:0.375rem 0.5rem;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;">descricao</td><td style="padding:0.375rem 0.5rem;">string</td><td style="padding:0.375rem 0.5rem;color:#888;">Nao</td><td style="padding:0.375rem 0.5rem;">Descricao da cobranca (max 255 chars)</td></tr>
                        <tr><td style="padding:0.375rem 0.5rem;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;">payer_name</td><td style="padding:0.375rem 0.5rem;">string</td><td style="padding:0.375rem 0.5rem;color:#888;">Nao</td><td style="padding:0.375rem 0.5rem;">Nome do pagador</td></tr>
                        <tr><td style="padding:0.375rem 0.5rem;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;">payer_cpf_cnpj</td><td style="padding:0.375rem 0.5rem;">string</td><td style="padding:0.375rem 0.5rem;color:#888;">Nao</td><td style="padding:0.375rem 0.5rem;">CPF ou CNPJ do pagador</td></tr>
                    </tbody>
                </table>

                <h4 style="font-size:0.75rem;font-weight:500;color:#666;margin-bottom:0.5rem;">Resposta (201 Created)</h4>
                <pre style="background:#000;border:1px solid #1a1a1a;border-radius:8px;padding:1rem;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;color:#888;line-height:1.6;margin:0;"><code>{
  "success": true,
  "data": {
    "transaction": {
      "id": 42,
      "amount": 29.90,
      "net_amount": 29.90,
      "fee_amount": 0.00,
      "fee_percent": 0,
      "status": "pending",
      "pix_copy_paste": "00020126360014br.gov.bcb.pix...",
      "pix_qrcode_url": "data:image/png;base64,...",
      "pix_expiration": "2026-06-25T14:30:00",
      "description": "Pagamento pedido #1234"
    }
  }
}</code></pre>
            </div>

            <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                    <span style="color:#888;font-family:'JetBrains Mono',monospace;font-size:0.75rem;font-weight:600;">GET</span>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:0.875rem;color:#fff;">/api/v1/pix/{id}</span>
                </div>
                <p style="font-size:0.8125rem;color:#888;margin-bottom:1rem;">Consulta o status e detalhes de uma transacao PIX existente.</p>
                <h4 style="font-size:0.75rem;font-weight:500;color:#666;margin-bottom:0.5rem;">Resposta (200 OK)</h4>
                <pre style="background:#000;border:1px solid #1a1a1a;border-radius:8px;padding:1rem;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;color:#888;line-height:1.6;margin:0;"><code>{
  "success": true,
  "data": {
    "transaction": {
      "id": 42,
      "amount": 29.90,
      "net_amount": 29.90,
      "status": "confirmed",
      "payer_name": "Joao Silva",
      "created_at": "2026-06-25 12:00:00",
      "updated_at": "2026-06-25 12:05:00"
    }
  }
}</code></pre>
            </div>

            <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                    <span style="color:#888;font-family:'JetBrains Mono',monospace;font-size:0.75rem;font-weight:600;">GET</span>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:0.875rem;color:#fff;">/api/v1/balance</span>
                </div>
                <p style="font-size:0.8125rem;color:#888;margin-bottom:1rem;">Retorna o saldo atual e totais do usuario.</p>
                <pre style="background:#000;border:1px solid #1a1a1a;border-radius:8px;padding:1rem;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:0.6875rem;color:#888;line-height:1.6;margin:0;"><code>{
  "success": true,
  "data": {
    "balance": {
      "current": 1250.75,
      "total_received": 5430.00,
      "total_withdrawn": 4179.25
    }
  }
}</code></pre>
            </div>
        </div>
    </div>

    <div style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem;font-weight:600;color:#ffffff;margin-bottom:1.5rem;">Rate Limits</h2>
        <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;">
            <p style="font-size:0.8125rem;color:#888;margin-bottom:1rem;">Cada API key possui um limite de requisicoes por minuto para garantir estabilidade.</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div style="background:#000;border:1px solid #1a1a1a;border-radius:6px;padding:1rem;">
                    <p style="font-size:2rem;font-family:'JetBrains Mono',monospace;font-weight:600;color:#fff;margin-bottom:0.25rem;">60</p>
                    <p style="font-size:0.75rem;color:#888;">requisicoes/minuto</p>
                    <p style="font-size:0.6875rem;color:#666;margin-top:4px;">Limite padrao por API key</p>
                </div>
                <div style="background:#000;border:1px solid #1a1a1a;border-radius:6px;padding:1rem;">
                    <p style="font-size:2rem;font-family:'JetBrains Mono',monospace;font-weight:600;color:#888;margin-bottom:0.25rem;">429</p>
                    <p style="font-size:0.75rem;color:#888;">Excedeu o limite</p>
                    <p style="font-size:0.6875rem;color:#666;margin-top:4px;">Resposta com header Retry-After</p>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem;font-weight:600;color:#ffffff;margin-bottom:1.5rem;">Webhooks <span style="display:inline-block;padding:2px 6px;border-radius:4px;font-size:0.6875rem;font-weight:400;color:#666;border:1px solid #1a1a1a;margin-left:0.5rem;">em breve</span></h2>
        <div style="background:#0a0a0a;border:1px solid #1a1a1a;border-radius:8px;padding:1.25rem;display:flex;align-items:flex-start;gap:0.75rem;">
            <div style="width:36px;height:36px;border-radius:8px;background:#0a0a0a;border:1px solid #1a1a1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            </div>
            <div>
                <h3 style="font-size:0.875rem;font-weight:500;color:#fff;margin-bottom:0.25rem;">Notificacoes em tempo real</h3>
                <p style="font-size:0.75rem;color:#888;">Em breve: configure um webhook para receber notificacoes quando um PIX for pago.</p>
            </div>
        </div>
    </div>
</section>

<footer style="border-top:1px solid #1a1a1a;padding:1.5rem;text-align:center;">
    <p style="font-size:0.75rem;color:#333;">&copy; 2026 AstraPay</p>
</footer>
