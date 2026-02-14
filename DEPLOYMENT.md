# Deployment Guide - pornguru.cam

## Git Workflow

```
feature/xyz  ──→  dev  ──→  main (production)
                   ↑          ↑
              test here    auto-deploys
```

| Branch | Purpose | Deploys to |
|--------|---------|------------|
| `main` | Production-ready code | pornguru.cam (auto via CI/CD) |
| `dev` | Integration & testing | Local dev stack |
| `feature/*` | New features / fixes | Nothing |

### Daily workflow

```bash
# 1. Start a feature
make feature F=fix-stream-quality

# 2. Work, commit, push
git add -A && git commit -m "fix stream quality selection"
git push -u origin feature/fix-stream-quality

# 3. When done, merge into dev
make finish                  # merges feature → dev

# 4. Test on dev
make dev                     # starts dev stack at localhost:8787

# 5. When ready, release to production
make release                 # merges dev → main, pushes, CI/CD deploys
```

## First-Time Server Setup

### 1. Clone and checkout

```bash
git clone git@github.com:kami0xff/pgcam.git /var/www/porngurucam
cd /var/www/porngurucam
```

### 2. Configure production environment

```bash
cp .env.production.example .env.production
nano .env.production
```

**Required values:**
- `APP_KEY` -- Generate: `docker run --rm -it php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`
- `CAM_DB_HOST` / `CAM_DB_PASSWORD` -- External cam models database
- `DB_PASSWORD` -- Local PostgreSQL for users/favorites/sessions

### 3. Deploy

```bash
./deploy.sh
```

This will build the Docker image, start containers, run migrations, build caches, and obtain HTTPS certificates automatically.

### 4. Point DNS

Add A records for your domain pointing to your server IP:

```
pornguru.cam      A    YOUR_SERVER_IP
www.pornguru.cam  A    YOUR_SERVER_IP
```

FrankenPHP (Caddy) will automatically obtain and renew Let's Encrypt certificates once DNS propagates.

## Commands

```bash
make help          # Show all available commands

# Development
make dev           # Start dev stack (port 8787)
make dev-down      # Stop dev stack
make dev-logs      # Tail dev logs
make test          # Run tests

# Production
make prod          # Full deploy (build + restart + migrate + cache)
make prod-logs     # Tail production logs
make prod-shell    # Shell into production container
make build         # Build image only (no restart)
```

## Manual deployment

If CI/CD is not set up yet:

```bash
git checkout main
git pull origin main
./deploy.sh
```

## CI/CD (GitHub Actions)

Pushing to `main` triggers the pipeline automatically:
1. Runs test suite
2. SSHs into server and runs `deploy.sh`

### Required GitHub Secrets

Go to **Settings → Secrets and variables → Actions** and add:

| Secret | Value |
|--------|-------|
| `SSH_HOST` | Your server IP |
| `SSH_PORT` | SSH port (default: 22) |
| `SSH_USERNAME` | SSH user |
| `SSH_PRIVATE_KEY` | Contents of `~/.ssh/id_ed25519` (private key) |
| `DEPLOY_PATH` | `/var/www/porngurucam` |
| `FLUX_USERNAME` | *(optional)* Flux UI username |
| `FLUX_LICENSE_KEY` | *(optional)* Flux UI license key |

## Architecture

```
Internet
   │
   ▼
┌──────────────────────────────────────┐
│  FrankenPHP (Caddy + PHP 8.3)        │
│  Automatic HTTPS · HTTP/2 · HTTP/3   │
│  Ports: 80, 443                       │
├──────────────────────────────────────┤
│  Laravel Application                  │
│  OPcache · Config/Route/View cache    │
├──────────────────────────────────────┤
│  PostgreSQL 16  │  Redis 7            │
│  Users/Sessions │  Cache/Queue        │
├──────────────────────────────────────┤
│  External PostgreSQL                  │
│  Cam models (read-only)               │
└──────────────────────────────────────┘
```

## Troubleshooting

```bash
# Check container status
docker compose -f docker-compose.prod.yml ps

# Check health
docker inspect porngurucam-app | grep -A 10 "Health"

# View all logs
docker compose -f docker-compose.prod.yml logs -f

# Restart everything
docker compose -f docker-compose.prod.yml restart

# Clear all caches
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear

# Force SSL certificate renewal
docker compose -f docker-compose.prod.yml exec app caddy reload --config /etc/caddy/Caddyfile
```
