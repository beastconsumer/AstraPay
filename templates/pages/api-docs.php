<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Docs - AstraPay</title>
    <meta name="description" content="Documentacao da API AstraPay - Integre PIX em minutos">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { zinc: { 950: '#09090b' } },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        body { background: #09090b; }
        .gradient-text { background: linear-gradient(135deg, #a78bfa, #8b5cf6, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .code-block { background: #18181b; border: 1px solid #27272a; border-radius: 8px; padding: 20px; overflow-x: auto; }
        .code-block code { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #e4e4e7; line-height: 1.8; }
        .endpoint-row { transition: all 0.15s ease; }
        .endpoint-row:hover { background: rgba(139, 92, 246, 0.05); }
        .method-get { color: #10b981; }
        .method-post { color: #8b5cf6; }
        .method-delete { color: #ef4444; }
        .hl-str { color: #a78bfa; }
        .hl-num { color: #fbbf24; }
        .hl-key { color: #60a5fa; }
        .hl-bool { color: #f472b6; }
        .hl-comment { color: #71717a; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-100 font-sans antialiased">

<nav class="fixed top-0 left-0 right-0 z-50 border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-xl">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2 text-white font-bold text-lg">
            <svg class="w-7 h-7 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            AstraPay
        </a>
        <div class="flex items-center gap-4 text-sm">
            <a href="/api/keys" class="text-zinc-400 hover:text-white transition-colors">Minhas API Keys</a>
            <a href="/dashboard" class="text-zinc-400 hover:text-white transition-colors">Dashboard</a>
            <a href="/login" class="px-4 py-1.5 bg-violet-500 hover:bg-violet-400 text-white rounded-md text-sm font-medium transition-colors">Entrar</a>
        </div>
    </div>
</nav>

<main class="pt-20 pb-32">
    <section class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/10 border border-violet-500/20 text-violet-400 text-sm font-medium mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                API v1
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <span class="gradient-text">AstraPay API</span>
            </h1>
            <p class="text-lg text-zinc-400 max-w-xl mx-auto">
                Integre PIX em minutos. Crie cobrancas, consulte status e gerencie transacoes com nossa API RESTful.
            </p>
            <div class="flex items-center justify-center gap-3 mt-8">
                <a href="#quickstart" class="px-6 py-2.5 bg-violet-500 hover:bg-violet-400 text-white rounded-lg font-medium transition-all duration-150">Quickstart</a>
                <a href="#endpoints" class="px-6 py-2.5 border border-zinc-700 hover:border-violet-500/50 text-zinc-300 hover:text-white rounded-lg font-medium transition-all duration-150">Endpoints</a>
            </div>
        </div>

        <section id="quickstart" class="mb-20">
            <h2 class="text-2xl font-bold mb-2">Quickstart</h2>
            <p class="text-zinc-400 mb-8">Obtenha sua API key no painel e comece a integrar em segundos.</p>

            <div class="grid gap-6">
                <div>
                    <h3 class="text-sm font-medium text-zinc-300 mb-3 flex items-center gap-2">
                        <span class="px-1.5 py-0.5 text-xs rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">curl</span>
                        Gerar um PIX
                    </h3>
                    <div class="code-block"><code>
<span class="hl-comment"># Crie uma cobranca PIX</span>
curl -X POST https://astrapay.com.br/api/v1/pix \
  -H <span class="hl-str">"Content-Type: application/json"</span> \
  -H <span class="hl-str">"X-Api-Key: astrapay_SUA_CHAVE_AQUI"</span> \
  -d <span class="hl-str">'{
    "valor": 29.90,
    "descricao": "Pagamento pedido #1234"
  }'</span>
                    </code></div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-zinc-300 mb-3 flex items-center gap-2">
                        <span class="px-1.5 py-0.5 text-xs rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 font-mono">JavaScript</span>
                        Fetch API
                    </h3>
                    <div class="code-block"><code>
<span class="hl-key">const</span> response = <span class="hl-key">await</span> fetch(<span class="hl-str">'https://astrapay.com.br/api/v1/pix'</span>, {
  method: <span class="hl-str">'POST'</span>,
  headers: {
    <span class="hl-str">'Content-Type'</span>: <span class="hl-str">'application/json'</span>,
    <span class="hl-str">'X-Api-Key'</span>: <span class="hl-str">'astrapay_SUA_CHAVE_AQUI'</span>,
  },
  body: JSON.stringify({
    valor: <span class="hl-num">29.90</span>,
    descricao: <span class="hl-str">'Pagamento pedido #1234'</span>,
  }),
});

<span class="hl-key">const</span> data = <span class="hl-key">await</span> response.json();
console.log(data);
                    </code></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-300 mb-3 flex items-center gap-2">
                            <span class="px-1.5 py-0.5 text-xs rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 font-mono">PHP</span>
                            cURL
                        </h3>
                        <div class="code-block"><code>
&lt;?php

$ch = curl_init(<span class="hl-str">'https://astrapay.com.br/api/v1/pix'</span>);
curl_setopt_array($ch, [
    CURLOPT_POST => <span class="hl-bool">true</span>,
    CURLOPT_RETURNTRANSFER => <span class="hl-bool">true</span>,
    CURLOPT_HTTPHEADER => [
        <span class="hl-str">'Content-Type: application/json'</span>,
        <span class="hl-str">'X-Api-Key: astrapay_SUA_CHAVE_AQUI'</span>,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        <span class="hl-str">'valor'</span> => <span class="hl-num">29.90</span>,
        <span class="hl-str">'descricao'</span> => <span class="hl-str">'Pedido #1234'</span>,
    ]),
]);
$data = json_decode(curl_exec($ch), true);
curl_close($ch);
                        </code></div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-300 mb-3 flex items-center gap-2">
                            <span class="px-1.5 py-0.5 text-xs rounded bg-teal-500/10 text-teal-400 border border-teal-500/20 font-mono">Python</span>
                            requests
                        </h3>
                        <div class="code-block"><code>
<span class="hl-key">import</span> requests

response = requests.post(
    <span class="hl-str">"https://astrapay.com.br/api/v1/pix"</span>,
    headers={
        <span class="hl-str">"Content-Type"</span>: <span class="hl-str">"application/json"</span>,
        <span class="hl-str">"X-Api-Key"</span>: <span class="hl-str">"astrapay_SUA_CHAVE_AQUI"</span>,
    },
    json={
        <span class="hl-str">"valor"</span>: <span class="hl-num">29.90</span>,
        <span class="hl-str">"descricao"</span>: <span class="hl-str">"Pedido #1234"</span>,
    },
)

data = response.json()
print(data)
                        </code></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="endpoints" class="mb-20">
            <h2 class="text-2xl font-bold mb-2">Endpoints</h2>
            <p class="text-zinc-400 mb-8">Base URL: <code class="px-2 py-0.5 bg-zinc-900 border border-zinc-800 rounded text-violet-400 font-mono text-sm">https://astrapay.com.br/api/v1</code></p>

            <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-800 bg-zinc-900/50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider w-16">Metodo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">URL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider w-24">Auth</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wider">Descricao</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-zinc-800/50 endpoint-row">
                                <td class="px-6 py-4"><span class="method-post font-mono font-semibold text-xs">POST</span></td>
                                <td class="px-6 py-4 font-mono text-sm">/pix</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs rounded-full bg-violet-500/10 text-violet-400 border border-violet-500/20">API Key</span></td>
                                <td class="px-6 py-4 text-zinc-300">Criar nova cobranca PIX</td>
                            </tr>
                            <tr class="border-b border-zinc-800/50 endpoint-row">
                                <td class="px-6 py-4"><span class="method-get font-mono font-semibold text-xs">GET</span></td>
                                <td class="px-6 py-4 font-mono text-sm">/pix/{id}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs rounded-full bg-violet-500/10 text-violet-400 border border-violet-500/20">API Key</span></td>
                                <td class="px-6 py-4 text-zinc-300">Consultar status de um PIX</td>
                            </tr>
                            <tr class="border-b border-zinc-800/50 endpoint-row">
                                <td class="px-6 py-4"><span class="method-get font-mono font-semibold text-xs">GET</span></td>
                                <td class="px-6 py-4 font-mono text-sm">/balance</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs rounded-full bg-violet-500/10 text-violet-400 border border-violet-500/20">API Key</span></td>
                                <td class="px-6 py-4 text-zinc-300">Consultar saldo do usuario</td>
                            </tr>
                            <tr class="endpoint-row">
                                <td class="px-6 py-4"><span class="method-get font-mono font-semibold text-xs">GET</span></td>
                                <td class="px-6 py-4 font-mono text-sm">/transactions</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs rounded-full bg-violet-500/10 text-violet-400 border border-violet-500/20">API Key</span></td>
                                <td class="px-6 py-4 text-zinc-300">Listar transacoes (paginado)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="mb-20">
            <h2 class="text-2xl font-bold mb-8">Detalhes dos Endpoints</h2>

            <div class="space-y-10">
                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="method-post font-mono font-bold text-sm">POST</span>
                        <span class="font-mono text-lg text-white">/api/v1/pix</span>
                    </div>
                    <p class="text-zinc-400 mb-6">Cria uma nova cobranca PIX. O pagador pode pagar usando QR Code ou copia-e-cola.</p>

                    <h4 class="text-sm font-semibold text-zinc-300 mb-3">Parametros (JSON Body)</h4>
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-800">
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Campo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Tipo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Obrigatorio</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Descricao</th>
                                </tr>
                            </thead>
                            <tbody class="text-zinc-300">
                                <tr class="border-b border-zinc-800/50">
                                    <td class="px-4 py-3 font-mono text-xs">valor</td>
                                    <td class="px-4 py-3">float</td>
                                    <td class="px-4 py-3"><span class="text-emerald-400">Sim</span></td>
                                    <td class="px-4 py-3">Valor da cobranca em reais (ex: 29.90)</td>
                                </tr>
                                <tr class="border-b border-zinc-800/50">
                                    <td class="px-4 py-3 font-mono text-xs">descricao</td>
                                    <td class="px-4 py-3">string</td>
                                    <td class="px-4 py-3">Nao</td>
                                    <td class="px-4 py-3">Descricao da cobranca (max 255 chars)</td>
                                </tr>
                                <tr class="border-b border-zinc-800/50">
                                    <td class="px-4 py-3 font-mono text-xs">payer_name</td>
                                    <td class="px-4 py-3">string</td>
                                    <td class="px-4 py-3">Nao</td>
                                    <td class="px-4 py-3">Nome do pagador</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs">payer_cpf_cnpj</td>
                                    <td class="px-4 py-3">string</td>
                                    <td class="px-4 py-3">Nao</td>
                                    <td class="px-4 py-3">CPF ou CNPJ do pagador</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 class="text-sm font-semibold text-zinc-300 mb-3">Resposta (201 Created)</h4>
                    <div class="code-block text-sm"><code>
{
  <span class="hl-str">"success"</span>: <span class="hl-bool">true</span>,
  <span class="hl-str">"data"</span>: {
    <span class="hl-str">"transaction"</span>: {
      <span class="hl-str">"id"</span>: <span class="hl-num">42</span>,
      <span class="hl-str">"amount"</span>: <span class="hl-num">29.90</span>,
      <span class="hl-str">"net_amount"</span>: <span class="hl-num">29.90</span>,
      <span class="hl-str">"fee_amount"</span>: <span class="hl-num">0.00</span>,
      <span class="hl-str">"fee_percent"</span>: <span class="hl-num">0</span>,
      <span class="hl-str">"status"</span>: <span class="hl-str">"pending"</span>,
      <span class="hl-str">"pix_copy_paste"</span>: <span class="hl-str">"00020126360014br.gov.bcb.pix..."</span>,
      <span class="hl-str">"pix_qrcode_url"</span>: <span class="hl-str">"data:image/png;base64,..."</span>,
      <span class="hl-str">"pix_expiration"</span>: <span class="hl-str">"2026-06-25T14:30:00"</span>,
      <span class="hl-str">"description"</span>: <span class="hl-str">"Pagamento pedido #1234"</span>
    }
  }
}
                    </code></div>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="method-get font-mono font-bold text-sm">GET</span>
                        <span class="font-mono text-lg text-white">/api/v1/pix/{id}</span>
                    </div>
                    <p class="text-zinc-400 mb-6">Consulta o status e detalhes de uma transacao PIX existente.</p>

                    <h4 class="text-sm font-semibold text-zinc-300 mb-3">Resposta (200 OK)</h4>
                    <div class="code-block text-sm"><code>
{
  <span class="hl-str">"success"</span>: <span class="hl-bool">true</span>,
  <span class="hl-str">"data"</span>: {
    <span class="hl-str">"transaction"</span>: {
      <span class="hl-str">"id"</span>: <span class="hl-num">42</span>,
      <span class="hl-str">"amount"</span>: <span class="hl-num">29.90</span>,
      <span class="hl-str">"net_amount"</span>: <span class="hl-num">29.90</span>,
      <span class="hl-str">"status"</span>: <span class="hl-str">"confirmed"</span>,
      <span class="hl-str">"payer_name"</span>: <span class="hl-str">"Joao Silva"</span>,
      <span class="hl-str">"created_at"</span>: <span class="hl-str">"2026-06-25 12:00:00"</span>,
      <span class="hl-str">"updated_at"</span>: <span class="hl-str">"2026-06-25 12:05:00"</span>
    }
  }
}
                    </code></div>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="method-get font-mono font-bold text-sm">GET</span>
                        <span class="font-mono text-lg text-white">/api/v1/balance</span>
                    </div>
                    <p class="text-zinc-400 mb-6">Retorna o saldo atual e totais do usuario.</p>

                    <h4 class="text-sm font-semibold text-zinc-300 mb-3">Resposta (200 OK)</h4>
                    <div class="code-block text-sm"><code>
{
  <span class="hl-str">"success"</span>: <span class="hl-bool">true</span>,
  <span class="hl-str">"data"</span>: {
    <span class="hl-str">"balance"</span>: {
      <span class="hl-str">"current"</span>: <span class="hl-num">1250.75</span>,
      <span class="hl-str">"total_received"</span>: <span class="hl-num">5430.00</span>,
      <span class="hl-str">"total_withdrawn"</span>: <span class="hl-num">4179.25</span>
    }
  }
}
                    </code></div>
                </div>

                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="method-get font-mono font-bold text-sm">GET</span>
                        <span class="font-mono text-lg text-white">/api/v1/transactions</span>
                    </div>
                    <p class="text-zinc-400 mb-6">Lista todas as transacoes do usuario, com paginacao e filtro por status.</p>

                    <h4 class="text-sm font-semibold text-zinc-300 mb-3">Query Parameters</h4>
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-800">
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Parametro</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Tipo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Default</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-zinc-400 uppercase">Descricao</th>
                                </tr>
                            </thead>
                            <tbody class="text-zinc-300">
                                <tr class="border-b border-zinc-800/50">
                                    <td class="px-4 py-3 font-mono text-xs">page</td>
                                    <td class="px-4 py-3">int</td>
                                    <td class="px-4 py-3">1</td>
                                    <td class="px-4 py-3">Numero da pagina</td>
                                </tr>
                                <tr class="border-b border-zinc-800/50">
                                    <td class="px-4 py-3 font-mono text-xs">limit</td>
                                    <td class="px-4 py-3">int</td>
                                    <td class="px-4 py-3">20</td>
                                    <td class="px-4 py-3">Itens por pagina (max 100)</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs">status</td>
                                    <td class="px-4 py-3">string</td>
                                    <td class="px-4 py-3">-</td>
                                    <td class="px-4 py-3">Filtrar por: pending, confirmed, received, refunded, cancelled</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-20">
            <h2 class="text-2xl font-bold mb-8">Rate Limits</h2>
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                <p class="text-zinc-400 mb-6">Cada API key possui um limite de requisicoes por minuto para garantir estabilidade.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-zinc-950 border border-zinc-800 rounded-lg p-4">
                        <div class="text-3xl font-mono font-bold text-violet-400 mb-1">60</div>
                        <div class="text-sm text-zinc-400">requisicoes/minuto</div>
                        <div class="text-xs text-zinc-500 mt-1">Limite padrao por API key</div>
                    </div>
                    <div class="bg-zinc-950 border border-zinc-800 rounded-lg p-4">
                        <div class="text-3xl font-mono font-bold text-amber-400 mb-1">429</div>
                        <div class="text-sm text-zinc-400">Excedeu o limite</div>
                        <div class="text-xs text-zinc-500 mt-1">Resposta com header Retry-After</div>
                    </div>
                </div>
                <div class="code-block mt-4 text-sm"><code>
<span class="hl-comment"># Resposta quando excede o limite:</span>
HTTP/1.1 <span class="hl-num">429</span> Too Many Requests
Retry-After: <span class="hl-num">45</span>

{
  <span class="hl-str">"success"</span>: <span class="hl-bool">false</span>,
  <span class="hl-str">"error"</span>: <span class="hl-str">"Rate limit exceeded. Try again in 45 seconds."</span>
}
                </code></div>
            </div>
        </section>

        <section class="mb-20">
            <h2 class="text-2xl font-bold mb-8">Webhooks (em breve)</h2>
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-violet-500/10 border border-violet-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white mb-2">Notificacoes em tempo real</h3>
                        <p class="text-zinc-400 text-sm">Em breve: configure um webhook para receber notificacoes quando um PIX for pago. Voce podera cadastrar uma URL e receberemos um POST com os dados da transacao assim que o pagamento for confirmado.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="sdks" class="mb-20">
            <h2 class="text-2xl font-bold mb-8">SDKs & Exemplos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="#" class="bg-zinc-900 border border-zinc-800 hover:border-violet-500/30 rounded-xl p-5 transition-all duration-200 group">
                    <h4 class="font-semibold text-white group-hover:text-violet-400 transition-colors mb-1">astra-pay-js</h4>
                    <p class="text-sm text-zinc-400">SDK JavaScript/TypeScript para Node.js e browser.</p>
                    <span class="inline-block mt-3 text-xs text-zinc-500 font-mono">npm install astra-pay</span>
                </a>
                <a href="#" class="bg-zinc-900 border border-zinc-800 hover:border-violet-500/30 rounded-xl p-5 transition-all duration-200 group">
                    <h4 class="font-semibold text-white group-hover:text-violet-400 transition-colors mb-1">astra-pay-php</h4>
                    <p class="text-sm text-zinc-400">SDK PHP com suporte a todos os endpoints.</p>
                    <span class="inline-block mt-3 text-xs text-zinc-500 font-mono">composer require astrapay/sdk</span>
                </a>
                <a href="#" class="bg-zinc-900 border border-zinc-800 hover:border-violet-500/30 rounded-xl p-5 transition-all duration-200 group">
                    <h4 class="font-semibold text-white group-hover:text-violet-400 transition-colors mb-1">astra-pay-py</h4>
                    <p class="text-sm text-zinc-400">SDK Python para integracao rapida.</p>
                    <span class="inline-block mt-3 text-xs text-zinc-500 font-mono">pip install astra-pay</span>
                </a>
                <a href="#" class="bg-zinc-900 border border-zinc-800 hover:border-violet-500/30 rounded-xl p-5 transition-all duration-200 group">
                    <h4 class="font-semibold text-white group-hover:text-violet-400 transition-colors mb-1">Exemplos no GitHub</h4>
                    <p class="text-sm text-zinc-400">Repositorio com exemplos prontos em varias linguagens.</p>
                    <span class="inline-block mt-3 text-xs text-zinc-500 font-mono">github.com/astrapay/examples</span>
                </a>
            </div>
        </section>
    </section>
</main>

<footer class="border-t border-zinc-800/50 py-12">
    <div class="max-w-4xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-zinc-500">
        <div class="flex items-center gap-2">
            <span>&copy; 2026 AstraPay</span>
            <span class="text-zinc-700">|</span>
            <span>Feito no Brasil</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="#" class="hover:text-zinc-300 transition-colors">Termos</a>
            <a href="#" class="hover:text-zinc-300 transition-colors">Privacidade</a>
            <a href="#" class="hover:text-zinc-300 transition-colors">Contato</a>
        </div>
    </div>
</footer>

</body>
</html>
