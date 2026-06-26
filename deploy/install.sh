#!/bin/bash
# AstraPay - First-time server installation
# Run as root on Ubuntu/Debian VPS

set -e

echo "============================================"
echo "  AstraPay - Server Installation"
echo "============================================"
echo ""

ASTRA_DIR="/root/astrapay"

echo "[1/8] Updating system packages..."
apt-get update -y && apt-get upgrade -y

echo "[2/8] Installing PHP + extensions..."
apt-get install -y php php-cli php-sqlite3 php-curl php-mbstring unzip curl nginx

echo "[3/8] Verifying PHP installation..."
php -v
echo "PHP installed successfully."

echo "[4/8] Creating directory structure..."
mkdir -p "$ASTRA_DIR/backend/public"
mkdir -p "$ASTRA_DIR/backend/storage/database"
mkdir -p "$ASTRA_DIR/backend/storage/logs"
mkdir -p "$ASTRA_DIR/templates/pages"
mkdir -p "$ASTRA_DIR/templates/layouts"
mkdir -p "$ASTRA_DIR/templates/components"
mkdir -p "$ASTRA_DIR/cron"
mkdir -p "$ASTRA_DIR/deploy"
mkdir -p /var/log/astrapay

echo "[5/8] Setting permissions..."
chmod -R 755 "$ASTRA_DIR"
chmod -R 775 "$ASTRA_DIR/backend/storage"

echo "[6/8] Installing systemd service..."
cp "$ASTRA_DIR/deploy/astrapay.service" /etc/systemd/system/astrapay.service
systemctl daemon-reload
systemctl enable astrapay

echo "[7/8] Setting up cron jobs..."
bash "$ASTRA_DIR/deploy/setup-cron.sh"

echo "[8/8] Setting up log rotation..."
cat > /etc/logrotate.d/astrapay << 'LOGROTATE'
/var/log/astrapay/*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
    create 0644 root root
}
LOGROTATE

echo ""
echo "============================================"
echo "  Installation Complete!"
echo "============================================"
echo ""
echo "  AstraPay Dir: $ASTRA_DIR"
echo "  Database:     $ASTRA_DIR/backend/storage/database/astrapay.sqlite"
echo "  Logs:         /var/log/astrapay/"
echo ""
echo "  Next steps:"
echo "  1. Upload project files (run deploy.sh)"
echo "  2. Initialize database"
echo "  3. Start service: systemctl start astrapay"
echo "  4. Check status: systemctl status astrapay"
echo ""
echo "  The server will be available at: http://0.0.0.0:8080"
echo ""
