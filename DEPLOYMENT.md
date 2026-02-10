# Deployment Guide - pornguru.cam

## Quick Start

### 1. Clone the repository on your server

```bash
git clone <your-repo-url> porngurucam
cd porngurucam
git checkout production
```

### 2. Configure environment

```bash
cp .env.production.example .env.production
nano .env.production  # Fill in your values
```

**Required values to set:**
- `APP_KEY` - Generate with: `php artisan key:generate --show`
- `CAM_DB_HOST` - Your PostgreSQL host for cam models
- `CAM_DB_PASSWORD` - PostgreSQL password
- `DB_PASSWORD` - MySQL password for users/favorites
- `DB_ROOT_PASSWORD` - MySQL root password

### 3. Deploy

```bash
./deploy.sh
```

That's it! The script will:
- Build the Docker image
- Start all containers
- Run migrations
- Cache configuration
- Set up automatic HTTPS via Let's Encrypt

## Manual Commands

### Build image
```bash
docker compose -f docker-compose.prod.yml build
```

### Start containers
```bash
docker compose -f docker-compose.prod.yml up -d
```

### View logs
```bash
docker compose -f docker-compose.prod.yml logs -f app
```

### Run artisan commands
```bash
docker compose -f docker-compose.prod.yml exec app php artisan <command>
```

### Access container shell
```bash
docker compose -f docker-compose.prod.yml exec app sh
```

### Stop containers
```bash
docker compose -f docker-compose.prod.yml down
```

## SSL/HTTPS

FrankenPHP (Caddy) handles SSL automatically:
- Certificates are obtained from Let's Encrypt
- Auto-renewal is handled automatically
- HTTP/2 and HTTP/3 are enabled by default
- Certificates are stored in the `caddy_data` volume

## Architecture

```
┌─────────────────────────────────────────┐
│  FrankenPHP (Caddy + PHP 8.3)           │
│  - Automatic HTTPS                      │
│  - Worker mode (persistent PHP)         │
│  - HTTP/2 & HTTP/3                      │
│  Ports: 80, 443                         │
├─────────────────────────────────────────┤
│  Laravel Application                    │
│  - Config/route/view caching            │
│  - OPcache enabled                      │
├─────────────────────────────────────────┤
│  MySQL 8.0                              │
│  - Users, favorites, sessions           │
├─────────────────────────────────────────┤
│  Redis 7                                │
│  - Sessions, cache, queue               │
├─────────────────────────────────────────┤
│  PostgreSQL (external)                  │
│  - Cam models data                      │
└─────────────────────────────────────────┘
```

## Updating

```bash
git pull origin production
./deploy.sh
```

## Troubleshooting

### Check container status
```bash
docker compose -f docker-compose.prod.yml ps
```

### Check container health
```bash
docker inspect porngurucam-app | grep -A 10 "Health"
```

### View Caddy/PHP logs
```bash
docker compose -f docker-compose.prod.yml logs -f app
```

### Restart containers
```bash
docker compose -f docker-compose.prod.yml restart
```

### Clear all caches
```bash
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear
```

### SSL certificate issues
Caddy stores certificates in a Docker volume. If you need to force renewal:
```bash
docker compose -f docker-compose.prod.yml exec app caddy reload --config /etc/caddy/Caddyfile
```
