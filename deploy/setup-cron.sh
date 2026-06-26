#!/bin/bash
# AstraPay - Cron job setup

ASTRA_DIR="/root/astrapay"
CRON_FILE="/tmp/astrapay_crontab"

cat > "$CRON_FILE" << 'CRON'
# AstraPay Cron Jobs
# Auto-withdrawal processing (every 5 minutes)
*/5 * * * * /usr/bin/php /root/astrapay/cron/withdrawals.php >> /var/log/astrapay/cron.log 2>&1

# Cleanup expired tokens + old login attempts (daily at midnight)
0 0 * * * /usr/bin/php /root/astrapay/cron/cleanup.php >> /var/log/astrapay/cron.log 2>&1

# Health check (every minute)
*/1 * * * * /usr/bin/php /root/astrapay/cron/health.php >> /var/log/astrapay/cron_health.log 2>&1

# Daily stats aggregation (daily at midnight)
0 0 * * * /usr/bin/php /root/astrapay/cron/stats.php >> /var/log/astrapay/cron.log 2>&1

CRON

crontab "$CRON_FILE"
rm -f "$CRON_FILE"

echo "Cron jobs installed successfully."
crontab -l
