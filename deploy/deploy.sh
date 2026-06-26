#!/bin/bash
# AstraPay - Deploy script
# Uploads all project files to VPS and restarts service

set -e

VPS_HOST="root@72.60.140.55"
VPS_PASS="Germaninho9949?"
VPS_PATH="/root/astrapay"
LOCAL_PATH="$(dirname "$(dirname "$0")")"

echo "============================================"
echo "  AstraPay - Deploy to VPS"
echo "============================================"
echo ""
echo "  Target: $VPS_HOST"
echo "  Path:   $VPS_PATH"
echo ""

if [ ! -f "$LOCAL_PATH/backend/public_api.php" ]; then
    echo "ERROR: Run from astrapay/ directory. Missing public_api.php"
    exit 1
fi

echo "[1/6] Installing sshpass (if needed)..."
which sshpass > /dev/null 2>&1 || apt-get install -y sshpass

SSH_CMD="sshpass -p '$VPS_PASS' ssh -o StrictHostKeyChecking=no"
SCP_CMD="sshpass -p '$VPS_PASS' scp -o StrictHostKeyChecking=no -r"

echo "[2/6] Creating remote directories..."
$SSH_CMD $VPS_HOST "mkdir -p $VPS_PATH/backend/public $VPS_PATH/backend/storage/database $VPS_PATH/backend/storage/logs $VPS_PATH/templates/pages $VPS_PATH/templates/layouts $VPS_PATH/templates/components $VPS_PATH/cron $VPS_PATH/deploy"

echo "[3/6] Uploading project files..."
$SCP_CMD "$LOCAL_PATH/backend/config.php" $VPS_HOST:$VPS_PATH/backend/
$SCP_CMD "$LOCAL_PATH/backend/db.php" $VPS_HOST:$VPS_PATH/backend/
$SCP_CMD "$LOCAL_PATH/backend/public_api.php" $VPS_HOST:$VPS_PATH/backend/
$SCP_CMD "$LOCAL_PATH/backend/api_keys.php" $VPS_HOST:$VPS_PATH/backend/

if [ -f "$LOCAL_PATH/backend/public/index.php" ]; then
    $SCP_CMD "$LOCAL_PATH/backend/public/index.php" $VPS_HOST:$VPS_PATH/backend/public/
fi

if [ -d "$LOCAL_PATH/backend/src" ]; then
    $SSH_CMD $VPS_HOST "mkdir -p $VPS_PATH/backend/src"
    $SCP_CMD "$LOCAL_PATH/backend/src/"* $VPS_HOST:$VPS_PATH/backend/src/
fi

if [ -d "$LOCAL_PATH/templates" ]; then
    $SSH_CMD $VPS_HOST "mkdir -p $VPS_PATH/templates/pages $VPS_PATH/templates/layouts $VPS_PATH/templates/components"
    if [ -f "$LOCAL_PATH/templates/pages/landing.php" ]; then
        $SCP_CMD "$LOCAL_PATH/templates/pages/"* $VPS_HOST:$VPS_PATH/templates/pages/
    fi
    if [ -f "$LOCAL_PATH/templates/layouts/main.php" ]; then
        $SCP_CMD "$LOCAL_PATH/templates/layouts/"* $VPS_HOST:$VPS_PATH/templates/layouts/
    fi
    if [ -d "$LOCAL_PATH/templates/components" ] && [ "$(ls -A $LOCAL_PATH/templates/components/ 2>/dev/null)" ]; then
        $SCP_CMD "$LOCAL_PATH/templates/components/"* $VPS_HOST:$VPS_PATH/templates/components/
    fi
fi

if [ -d "$LOCAL_PATH/cron" ]; then
    $SCP_CMD "$LOCAL_PATH/cron/"* $VPS_HOST:$VPS_PATH/cron/ 2>/dev/null || true
fi

$SCP_CMD "$LOCAL_PATH/deploy/"* $VPS_HOST:$VPS_PATH/deploy/ 2>/dev/null || true

echo "[4/6] Setting remote permissions..."
$SSH_CMD $VPS_HOST "chmod -R 755 $VPS_PATH && chmod -R 775 $VPS_PATH/backend/storage"

echo "[5/6] Initializing database..."
$SSH_CMD $VPS_HOST "php -r \"
require_once '$VPS_PATH/backend/config.php';
require_once '$VPS_PATH/backend/db.php';
\$db = Database::getInstance();
\$db->execute(\\\"CREATE TABLE IF NOT EXISTS api_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    api_key TEXT NOT NULL UNIQUE,
    name TEXT DEFAULT 'Default',
    rate_limit INTEGER DEFAULT 60,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT (datetime(\\\\'now\\\\')),
    last_used_at TEXT DEFAULT NULL
)\\\");
echo 'Database initialized.\n';
\""

echo "[6/6] Installing service and restarting..."
$SSH_CMD $VPS_HOST "cp $VPS_PATH/deploy/astrapay.service /etc/systemd/system/astrapay.service && systemctl daemon-reload && systemctl enable astrapay && systemctl restart astrapay"

echo ""
echo "============================================"
echo "  Deploy Complete!"
echo "============================================"
echo ""
echo "  Checking service status..."
$SSH_CMD $VPS_HOST "systemctl status astrapay --no-pager -l" || true
echo ""
echo "  AstraPay should be running at: http://72.60.140.55:8080"
echo ""
