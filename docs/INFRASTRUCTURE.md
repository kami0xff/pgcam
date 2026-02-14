# Infrastructure & Deployment Guide

How pornguru.cam runs, from development to production, on a single VPS.

---

## Table of Contents

1. [Server Overview](#server-overview)
2. [How the Two Docker Stacks Work](#how-the-two-docker-stacks-work)
3. [Git Branching & Workflow](#git-branching--workflow)
4. [DNS & HTTPS](#dns--https)
5. [The Deploy Process](#the-deploy-process)
6. [CI/CD (GitHub Actions)](#cicd-github-actions)
7. [Running Multiple Sites on One Server](#running-multiple-sites-on-one-server)
8. [Common Tasks Reference](#common-tasks-reference)

---

## Server Overview

Everything runs on a single Debian VPS at `91.208.175.92`. The server hosts
multiple Docker projects, each on its own port:

```
91.208.175.92
├── porngurucam (PRODUCTION)  → ports 80, 443    (FrankenPHP + Caddy)
├── porngurucam (DEV)         → port 8787        (PHP dev server + Vite)
├── tiklove                   → port 8888
├── sexemodellive             → port 8889
├── sexmarseille              → port 8082
├── pguru-dev                 → port 5555
├── xlove-pugo-admin          → port 4444
└── pugo-test-dev             → port 4545
```

Each project runs its own isolated Docker containers with their own database,
network, and config. They don't interfere with each other because:

- Each compose file has its own **project name** (`pgcam-dev`, `pgcam-prod`)
- Each project uses **different host ports** (8787, 8888, etc.)
- Each project has its own **Docker network** (containers can only talk to
  siblings within their compose stack)

### What was there before

Apache2 was installed on the server but only served the default "It works" page.
It was never configured as a reverse proxy. We disabled it to free port 80 for
the porngurucam production container:

```bash
sudo systemctl stop apache2
sudo systemctl disable apache2
```

If you ever need Apache back: `sudo systemctl enable --now apache2`

---

## How the Two Docker Stacks Work

Having two separate Docker Compose files is a standard pattern. The dev and prod
stacks serve completely different purposes, so separate files are clearer than
trying to merge them with profiles or overrides.

### Development Stack (`docker-compose.dev.yml`)

```
make dev          # start
make dev-down     # stop
make dev-logs     # tail logs
```

What it runs:

| Container | Purpose | Port |
|-----------|---------|------|
| `porngurucam-app-dev` | PHP 8.3 dev server (artisan serve) | 8787 |
| `porngurucam-vite` | Vite HMR for live CSS/JS reload | 5173 |
| `porngurucam-db` | PostgreSQL 16 (local users/sessions DB) | 5432 |

Key characteristics:
- **Source code is mounted** as a volume (`.:/var/www/html`), so edits show
  instantly without rebuilding
- **Debug mode on** (`APP_DEBUG=true`)
- **No caching** -- every request loads fresh config, routes, views
- **Vite HMR** -- CSS/JS changes appear in the browser without refresh
- Uses your local `.env` file

### Production Stack (`docker-compose.prod.yml`)

```
make prod         # full deploy (build + restart + migrate + cache)
make prod-up      # just start (no rebuild)
make prod-down    # stop
make prod-logs    # tail logs
```

What it runs:

| Container | Purpose | Port |
|-----------|---------|------|
| `porngurucam-app` | FrankenPHP (Caddy + PHP 8.3) | 80, 443 |
| `porngurucam-db-prod` | PostgreSQL 16 (local users/sessions DB) | internal |
| `porngurucam-redis` | Redis 7 (sessions, cache, queue) | internal |

Key characteristics:
- **Code is baked into the image** -- no volume mounts, the Docker image
  contains a frozen copy of the app (built from the `main` branch)
- **Multi-stage Dockerfile** (`Dockerfile.frankenphp`):
  1. Stage 1: Composer installs PHP dependencies
  2. Stage 2: Node builds frontend assets (Vite)
  3. Stage 3: FrankenPHP image with everything copied in
- **OPcache enabled** -- PHP bytecode cached, no file reads per request
- **Config/route/view caching** -- `php artisan optimize` pre-compiles everything
- **Automatic HTTPS** -- Caddy obtains Let's Encrypt certificates automatically
- **Redis** handles sessions, cache, and queue (faster than file/DB)
- Uses `.env.production` (separate from dev `.env`)

### Why Two Compose Files is Fine

Some people use `docker-compose.yml` + `docker-compose.override.yml`, but
explicit separate files are better here because:

- The stacks are fundamentally different (dev server vs FrankenPHP, no Redis
  vs Redis, mounted volumes vs baked image)
- You can run **both at the same time** (dev on 8787, prod on 80/443)
- It's immediately clear which file does what
- Each has its own project name so `docker compose` commands never cross-contaminate

### The .dockerignore File

This is critical. Without it, `COPY . .` in the Dockerfile sends your entire
local directory (including `vendor/`, `node_modules/`, `.git/`) to the Docker
build. This causes:

- Massive build context (slow builds)
- Local `vendor/` overwriting the clean Composer install inside Docker
- Broken autoloading in production

Our `.dockerignore` excludes:
```
vendor/          # Composer builds its own clean install
node_modules/    # npm ci builds its own clean install
.git/            # Not needed in the image
.env*            # Secrets stay out of the image
public/build/    # Vite rebuilds assets in the image
```

---

## Git Branching & Workflow

Simple three-tier model:

```
feature/xyz  ──→  dev  ──→  main
                   │          │
              test here    deploys to
              (port 8787)  production
```

### Branches

| Branch | Purpose | Pushes to |
|--------|---------|-----------|
| `main` | Production-ready code. Pushing here triggers deployment. | `origin/main` |
| `dev` | Integration branch. Merge features here, test locally. | `origin/dev` |
| `feature/*` | Individual features or fixes. Branch off `dev`. | `origin/feature/*` |

### Daily Workflow

```bash
# 1. Create a feature branch
make feature F=improve-stream-quality

# 2. Work on it, make commits
git add -A && git commit -m "improve HLS quality selection"

# 3. Push the feature branch (optional, for backup/collaboration)
git push -u origin feature/improve-stream-quality

# 4. When the feature is done, merge it into dev
make finish
# This does: checkout dev → pull → merge feature → push dev → delete feature branch

# 5. Test on the dev stack
make dev
# Open http://localhost:8787 and verify everything works

# 6. When dev is stable, release to production
make release
# This does: checkout main → pull → merge dev → push main → checkout dev
# If CI/CD is configured, this auto-deploys. Otherwise: make prod
```

### What `make release` Does Step by Step

```
1. git checkout main
2. git pull origin main         ← get any changes
3. git merge dev                ← bring dev changes into main
4. git push origin main         ← push to GitHub
5. git checkout dev             ← switch back to dev
```

If GitHub Actions CI/CD is configured, the push to `main` triggers:
- Run tests
- SSH into the server
- Run `./deploy.sh`

If CI/CD is not configured, after `make release` just run `make prod` manually.

---

## DNS & HTTPS

### How It Works

FrankenPHP includes Caddy, which has automatic HTTPS built in:

1. You point your domain's DNS A records to your server IP
2. Caddy detects incoming requests for that domain
3. Caddy automatically obtains a Let's Encrypt certificate (via HTTP-01 or TLS-ALPN-01 challenge)
4. All HTTP traffic is redirected to HTTPS
5. Certificates auto-renew before expiry

### DNS Setup

Go to your domain registrar and set these A records:

```
Type    Name    Value             TTL
A       @       91.208.175.92     300
A       www     91.208.175.92     300
```

`@` means the root domain (`pornguru.cam`). `www` is the subdomain
(`www.pornguru.cam`).

### How Caddy Routes Requests

The `Caddyfile` (baked into the Docker image) has two server blocks:

```
pornguru.cam, www.pornguru.cam {
    # Redirect www → non-www
    # Serve Laravel via FrankenPHP
    # HSTS, compression, caching headers
}

:80 {
    # Fallback for direct IP access or before DNS is configured
    # Serves Laravel on plain HTTP
}
```

When DNS doesn't point to the server yet, Caddy serves on the `:80` fallback
block. Certificate issuance will fail (and retry periodically) until DNS
propagates. This is expected and non-blocking -- the app still works on HTTP.

### Verifying DNS

```bash
# Check what IP your domain resolves to
dig +short pornguru.cam

# Should return: 91.208.175.92
# If it still shows the old IP, DNS hasn't propagated yet (can take 1-48 hours)
```

### Is Using the IP Directly OK?

Yes. When someone types `http://91.208.175.92` in their browser, the request
arrives at port 80 on your server. The `porngurucam-app` container is mapped
to port 80, so it receives the request. Caddy's `:80` fallback block handles
it and serves the Laravel app.

When DNS is configured:
- `pornguru.cam` resolves to `91.208.175.92`
- The request arrives at the same port 80
- Caddy sees the `Host: pornguru.cam` header and uses the main server block
- Caddy obtains a certificate and redirects to HTTPS

So the IP works now, and the domain will work once DNS is updated. No changes
needed on the server side.

---

## The Deploy Process

### What `./deploy.sh` Does

```
1.  Pull latest code from origin/main
2.  Build the Docker image (multi-stage: composer → node → frankenphp)
3.  Restart the app container with the new image
4.  Wait for the database to be healthy
5.  Run database migrations
6.  Clear old caches
7.  Build new caches (config, routes, views, events)
8.  Generate sitemaps
9.  Health check (curl http://localhost/up)
10. Print status
```

### Zero-Downtime Considerations

The current setup has a brief ~2-3 second gap during container restart. For a
cam aggregator where content updates every 30 seconds anyway, this is fine.

If you need true zero-downtime later, options include:
- Blue-green deployment (run two app containers, swap traffic)
- Rolling updates with Docker Swarm or Kubernetes

### Manual Deploy

```bash
# On the server:
cd /var/www/porngurucam
git checkout main
git pull origin main
./deploy.sh
```

### The Production `.env.production`

This file contains secrets and is NOT in git (listed in `.gitignore`). You must
create it manually on the server:

```bash
cp .env.production.example .env.production
nano .env.production
```

Key values to set:
- `APP_KEY` -- encryption key (generate once, never change)
- `DB_PASSWORD` -- local PostgreSQL password for users/sessions
- `CAM_DB_HOST` / `CAM_DB_PASSWORD` -- external cam models database
- `REDIS_HOST=redis` -- Redis container hostname (within Docker network)

---

## CI/CD (GitHub Actions)

The workflow file lives at `.github/workflows/deploy.yml`.

### What It Does

On every push to `main`:

```
┌─────────────────┐     ┌─────────────────┐
│  Job 1: Test    │────▶│  Job 2: Deploy  │
│                 │     │                 │
│ - Setup PHP 8.3 │     │ - SSH into VPS  │
│ - Setup Node 20 │     │ - Run deploy.sh │
│ - Composer inst │     │                 │
│ - npm ci + build│     │                 │
│ - Run tests     │     │                 │
└─────────────────┘     └─────────────────┘
         │                      │
    Tests must pass        Only on push
    (PRs too)             (not on PRs)
```

Pull requests to `main` trigger the test job only (no deploy).

### Required GitHub Secrets

Go to your repo **Settings → Secrets and variables → Actions** and add:

| Secret | Value | Notes |
|--------|-------|-------|
| `SSH_HOST` | `91.208.175.92` | Your server IP |
| `SSH_PORT` | `22` | Default SSH port |
| `SSH_USERNAME` | `joshua` | Your SSH user |
| `SSH_PRIVATE_KEY` | *(contents of private key)* | The full key file content |
| `DEPLOY_PATH` | `/var/www/porngurucam` | Where the project lives |

To get your private key content:
```bash
cat ~/.ssh/id_ed25519_github_kami0xff
# Copy the entire output including -----BEGIN and -----END lines
```

**Note:** The deploy key needs to be an SSH key that the server accepts for
login (in `~/.ssh/authorized_keys`), not necessarily the GitHub key.

---

## Running Multiple Sites on One Server

### Current Setup (One Production Site)

Right now, `porngurucam-app` is mapped directly to ports 80/443 on the host:

```
Internet → 91.208.175.92:80/443 → porngurucam-app container
```

This is simple and works great for a single production site.

### The Problem with Multiple Domains

If you later want `tiklove.com` and `sexemodellive.com` also running in
production on this same server, they can't all use ports 80/443. Only one
process can bind to a port.

### The Solution: A Shared Reverse Proxy

You'd put a single reverse proxy container on ports 80/443 that routes based
on the domain name:

```
Internet
    │
    ▼
┌───────────────────────────────────────┐
│  Reverse Proxy (ports 80/443)         │
│  (Caddy, Nginx, or Traefik)          │
│                                       │
│  pornguru.cam    → porngurucam:8080   │
│  tiklove.com     → tiklove:8081       │
│  sexemodel.live  → sexemodellive:8082 │
└───────────────────────────────────────┘
```

Each app container would listen on an internal port (not mapped to the host).
The reverse proxy handles SSL for all domains.

### How to Set This Up (When Needed)

The simplest option is a standalone Caddy container:

```yaml
# docker-compose.proxy.yml (shared across all projects)
services:
  proxy:
    image: caddy:2-alpine
    container_name: caddy-proxy
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
      - "443:443/udp"
    volumes:
      - ./Caddyfile.proxy:/etc/caddy/Caddyfile
      - caddy_data:/data
      - caddy_config:/config
    networks:
      - proxy-network

volumes:
  caddy_data:
  caddy_config:

networks:
  proxy-network:
    external: true
```

```
# Caddyfile.proxy
pornguru.cam {
    reverse_proxy porngurucam-app:8080
}

tiklove.com {
    reverse_proxy tiklove-app:8081
}
```

Each project's compose file would join the `proxy-network` and remove direct
port 80/443 mappings. This is a future optimization -- not needed until you
have a second production domain on this server.

---

## Common Tasks Reference

### Starting & Stopping

```bash
make dev              # Start dev (port 8787)
make dev-down         # Stop dev
make prod             # Full production deploy
make prod-up          # Start prod without rebuilding
make prod-down        # Stop prod
```

### Logs & Debugging

```bash
make dev-logs         # Tail dev app logs
make prod-logs        # Tail production app logs
make dev-shell        # Shell into dev container
make prod-shell       # Shell into prod container

# View specific container logs
docker logs -f porngurucam-app        # prod
docker logs -f porngurucam-app-dev    # dev
```

### Database

```bash
make migrate          # Run migrations (dev)
make fresh            # Fresh migrate + seed (dev)

# Production migrations (run automatically by deploy.sh)
docker compose --env-file .env.production -f docker-compose.prod.yml \
  exec -T app php artisan migrate --force
```

### Git Workflow

```bash
make feature F=my-feature    # Create feature/my-feature from dev
make finish                  # Merge current feature → dev
make release                 # Merge dev → main (triggers deploy)
```

### Cache Management

```bash
# Clear all caches (production)
docker compose --env-file .env.production -f docker-compose.prod.yml \
  exec app php artisan optimize:clear

# Rebuild caches (production)
docker compose --env-file .env.production -f docker-compose.prod.yml \
  exec app php artisan optimize
```

### Container Status

```bash
docker ps                              # All running containers
docker ps --filter "name=porngurucam"  # Just this project
docker stats --no-stream               # CPU/memory usage
```

---

## File Structure Summary

```
/var/www/porngurucam/
├── .dockerignore              ← Prevents local vendor/node_modules from polluting builds
├── .env                       ← Dev environment variables
├── .env.production            ← Prod environment variables (NOT in git)
├── .env.production.example    ← Template for .env.production
├── .github/workflows/
│   └── deploy.yml             ← CI/CD: test on push, deploy on main
├── Caddyfile                  ← FrankenPHP/Caddy web server config
├── Dockerfile                 ← Dev image (PHP CLI + artisan serve)
├── Dockerfile.frankenphp      ← Prod image (multi-stage FrankenPHP build)
├── Makefile                   ← Convenience commands (make dev, make prod, etc.)
├── deploy.sh                  ← Production deployment script
├── docker-compose.dev.yml     ← Dev stack (app + vite + db)
├── docker-compose.prod.yml    ← Prod stack (frankenphp + db + redis)
├── docker/
│   ├── nginx.conf             ← (legacy, used by old Dockerfile only)
│   └── supervisord.conf       ← (legacy, used by old Dockerfile only)
└── docs/
    ├── CI_CD_TUTORIAL.md      ← How to set up GitHub Actions
    ├── INFRASTRUCTURE.md      ← This file
    └── SSH_TUTORIAL.md        ← How to set up SSH keys
```
