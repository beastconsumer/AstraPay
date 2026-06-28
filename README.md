# AstraPay

> Multi-tenant PIX payment platform — generate, receive, and auto-transfer PIX payments.

![AstraPay](public/assets/logoescrita.png)

## Overview

AstraPay is a white-label PIX fintech platform. Multiple users (Zezinho, Juninho, Maria) create accounts, generate PIX charges, receive payments, and get auto-paid to their own PIX keys. All money flows through a single Asaas account with configurable admin fees.

**Live Demo:** [http://72.60.140.55:9000](http://72.60.140.55:9000)

---

## Features

### User Side
- **Dashboard** — Real-time stats, chart.js volume graph, recent transactions
- **PIX Generator** — QR code + copy-paste code, auto-refresh status polling
- **Transaction History** — Status filter tabs (All/Pending/Paid/Expired), pagination
- **Settings** — Profile, PIX key management, password change, tier limits
- **API Keys** — Generate/revoke/rotate keys for developer integration
- **Email Verification** — Resend-powered magic link verification
- **Password Recovery** — Forgot password flow with secure tokens

### Public API (for developers)
- `POST /api/v1/pix` — Create PIX charge (X-Api-Key auth)
- `GET /api/v1/pix/{id}` — Check payment status
- `GET /api/v1/balance` — Account balance
- `GET /api/v1/transactions` — Transaction history
- Rate limited (60 req/min per key)
- Full API docs at `/api-docs`

### Admin Panel
- **Dashboard** — Platform stats, recent users, pending withdrawals
- **User Management** — Search, filter, ban/unban, change tier, adjust limits
- **Transaction Monitor** — All transactions across all users
- **Audit Log** — Track admin actions
- **Settings** — Platform config, tier defaults, Asaas keys

### Security
- `bcrypt` cost 12 password hashing
- 64-char hex session tokens (24h expiry)
- CSRF protection, IP-based rate limiting
- CPF validation (Brazil check-digit algorithm)
- Name validation (minimum 2 words)
- Progressive tier limits (New → Basic → Bronze → Silver → Gold)
- Admin IP whitelist support
- X-Content-Type-Options, X-Frame-Options headers
- Prepared SQL statements throughout

### Anti-Fraud
- CPF validated via algoritmo oficial (dígitos verificadores)
- Email verification required to unlock tier upgrades
- Progressive limits per tier (daily/monthly/per-transaction)
- Auto-hold for suspicious volume (7 rules)
- IP tracking at registration and login
- Admin manual review for high-volume accounts

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.3 |
| Database | SQLite 3 (WAL mode) |
| Frontend | Vanilla JS + Tailwind CSS CDN + Chart.js |
| Fonts | Space Grotesk (headings) + Inter (body) + JetBrains Mono (code/numbers) |
| Payments | [Asaas API v3](https://docs.asaas.com/) |
| Email | [Resend](https://resend.com) |
| Deployment | systemd + nginx on Ubuntu VPS |
| Design | Custom black & white minimal theme |

---

## Project Structure

```
astrapay/
├── backend/
│   ├── config.php              # DB path, Asaas keys, app URL
│   ├── db.php                  # PDO SQLite singleton
│   ├── init_db.php             # Database migration
│   ├── router.php              # API route dispatcher
│   ├── middleware.php           # Auth, rate limit, CSRF, CPF validation
│   ├── auth.php                # Register, login, verify email, forgot password
│   ├── asaas.php               # Asaas API wrapper (PIX + transfer)
│   ├── pix_api.php             # PIX create/status/list/stats
│   ├── webhook.php             # Asaas payment webhook receiver
│   ├── withdraw_api.php        # Manual withdrawal endpoints
│   ├── withdraw_processor.php  # Auto-withdrawal engine
│   ├── stats_api.php           # Dashboard stats
│   ├── admin_api.php           # Admin CRUD endpoints
│   ├── admin_auth.php          # Admin authentication
│   ├── public_api.php          # Public REST API (X-Api-Key)
│   ├── api_keys.php            # API key management
│   ├── resend.php              # Email sending (Resend API)
│   └── public/                 # Admin panel frontend
├── public/
│   ├── index.php               # Front controller + static file server
│   ├── assets/
│   │   ├── css/app.css         # Design system (200+ classes)
│   │   ├── js/app.js           # Auth, API client, toast, masks
│   │   ├── logoescrita.png     # Text logo
│   │   ├── logobola.png        # Logo icon
│   │   ├── hero-bg.mp4         # Landing hero video
│   │   └── login-bg.mp4        # Login background video
│   ├── email/                  # Email templates (verify, reset, welcome, payment)
│   └── templates/
│       ├── layout.php          # HTML5 shell (3 layouts: public/auth/app)
│       ├── admin_layout.php    # Admin panel shell
│       └── pages/
│           ├── landing.php     # Public homepage
│           ├── login.php       # Login form
│           ├── register.php    # Registration form
│           ├── dashboard.php   # User dashboard
│           ├── pix.php         # PIX generator
│           ├── transactions.php # Transaction history
│           ├── settings.php    # User settings
│           ├── api-docs.php    # API documentation
│           ├── api-keys.php    # API key management
│           ├── verify-email.php
│           ├── forgot-password.php
│           └── reset-password.php
│           └── admin/          # Admin panel pages
├── cron/
│   ├── withdrawals.php         # Process pending withdrawals
│   ├── cleanup.php             # Clean expired tokens/logs
│   ├── health.php              # Health check
│   └── stats.php               # Daily stats aggregation
├── deploy/
│   ├── install.sh              # VPS first-time setup
│   ├── astrapay.service        # systemd unit file
│   ├── deploy.sh               # SCP-based deploy script
│   └── setup-cron.sh           # Cron job installer
├── data/
│   └── astrapay.db             # SQLite database (WAL mode)
└── README.md
```

---

## API Reference

### Authentication

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/register` | None | Create account |
| POST | `/api/auth/login` | None | Login |
| GET | `/api/auth/me` | Bearer | Get profile |
| POST | `/api/auth/forgot-password` | None | Request reset |
| POST | `/api/auth/reset-password` | None | Reset password |
| POST | `/api/auth/send-verification` | Bearer | Resend verify email |

### PIX

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/pix/create` | Bearer | Create PIX charge |
| GET | `/api/pix/status?id=X` | Bearer | Check status |
| GET | `/api/pix/list` | Bearer | Transaction history |
| GET | `/api/user/stats` | Bearer | Dashboard stats |

### Public API (X-Api-Key)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/pix` | X-Api-Key | Create PIX |
| GET | `/api/v1/pix/{id}` | X-Api-Key | Check status |
| GET | `/api/v1/balance` | X-Api-Key | Account balance |
| GET | `/api/v1/transactions` | X-Api-Key | Transaction list |

### Admin

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/admin/stats` | Bearer (admin) | Platform stats |
| GET | `/api/admin/users` | Bearer (admin) | User list |
| POST | `/api/admin/users/{id}/ban` | Bearer (admin) | Ban user |
| POST | `/api/admin/users/{id}/tier` | Bearer (admin) | Change tier |

Full documentation at `/api-docs`.

---

## Quick Start

### Prerequisites
- PHP 8.0+ with extensions: `sqlite3`, `curl`, `mbstring`
- Asaas account (production API key)
- Resend account (API key for email)

### Local Development
```bash
git clone https://github.com/beastconsumer/AstraPay.git
cd astrapay

# Initialize database
php backend/init_db.php

# Start PHP development server
cd public
php -S localhost:8080 index.php
```

### Production Deploy
```bash
# VPS setup
chmod +x deploy/install.sh && ./deploy/install.sh

# Deploy files
chmod +x deploy/deploy.sh && ./deploy/deploy.sh

# Service is auto-started by systemd
systemctl status astrapay
```

### Cron Jobs
```bash
*/5 *  * * * /usr/bin/php /root/astrapay/cron/withdrawals.php
0   0  * * * /usr/bin/php /root/astrapay/cron/cleanup.php
*/1 *  * * * /usr/bin/php /root/astrapay/cron/health.php
0   0  * * * /usr/bin/php /root/astrapay/cron/stats.php
```

---

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `ASAAS_API_KEY` | Asaas production API key | Required |
| `RESEND_API_KEY` | Resend email API key | Required |
| `APP_URL` | Public URL | `http://localhost:8080` |
| `DB_PATH` | SQLite database path | `data/astrapay.db` |

---

## Design System

- **Background:** `#000000` (pure black)
- **Cards:** `#0a0a0a` with `1px #141414` border, `12px` radius
- **Text:** `#ffffff` headings, `#888888` body, `#666666` muted
- **Accent:** White (`#ffffff`) for primary, no colors
- **Success:** `#22c55e`, **Danger:** `#ef4444`, **Warning:** `#f59e0b`
- **Fonts:** Space Grotesk (headings), Inter (body), JetBrains Mono (code/numbers)
- **Spacing:** 8px grid system, `24px` card padding, `48px` input height
- **Animations:** Subtle fade-in, card hover elevation, button ripple

---

## License

MIT © 2026 AstraPay
