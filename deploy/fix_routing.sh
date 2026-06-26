#!/bin/bash
# Fix AstraPay VPS routing - serve user frontend + admin + API
systemctl stop astrapay

# Create combined router
cat > /root/astrapay/public/combined.php << 'PHPEOF'
<?php
$uri = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
$uri = rtrim($uri, "/") ?: "/";

if (strpos($uri, "/api/") === 0) {
    require_once __DIR__ . "/../backend/config.php";
    require_once __DIR__ . "/../backend/db.php";
    require_once __DIR__ . "/../backend/middleware.php";
    require_once __DIR__ . "/../backend/router.php";
    routerHandleRequest();
    exit;
}

$page = $uri === "/" ? "landing" : ltrim($uri, "/");
$template = __DIR__ . "/templates/pages/{$page}.php";
if (file_exists($template)) {
    require_once __DIR__ . "/templates/layout.php";
} else {
    http_response_code(404);
    echo "<html><body style='background:#09090b;color:#a1a1aa;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh'><h1>404</h1></body></html>";
}
PHPEOF

# Update systemd
cat > /etc/systemd/system/astrapay.service << 'SVCEOF'
[Unit]
Description=AstraPay Fintech Server
After=network.target
[Service]
Type=simple
User=root
WorkingDirectory=/root/astrapay/public
ExecStart=/usr/bin/php -S 0.0.0.0:9000 combined.php
Restart=always
RestartSec=5
StandardOutput=append:/var/log/astrapay/server.log
StandardError=append:/var/log/astrapay/server_error.log
[Install]
WantedBy=multi-user.target
SVCEOF

systemctl daemon-reload
systemctl start astrapay
echo "Done"
