#!/bin/bash
# ==============================================
# Production Deployment Script for pornguru.cam
# Usage: ./deploy.sh
# ==============================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

COMPOSE="docker compose --env-file .env.production -f docker-compose.prod.yml"
BRANCH="main"

log()  { echo -e "${BLUE}[deploy]${NC} $1"; }
ok()   { echo -e "${GREEN}[  ok  ]${NC} $1"; }
warn() { echo -e "${YELLOW}[ warn ]${NC} $1"; }
err()  { echo -e "${RED}[error ]${NC} $1"; }

# --------------------------------------------------
# Pre-flight checks
# --------------------------------------------------
log "Starting deployment..."

if [ ! -f ".env.production" ]; then
    err ".env.production not found!"
    echo "  cp .env.production.example .env.production"
    echo "  Then fill in your values."
    exit 1
fi

# --------------------------------------------------
# Pull latest code
# --------------------------------------------------
if [ -d ".git" ]; then
    log "Pulling latest from origin/${BRANCH}..."
    git fetch origin
    git checkout "${BRANCH}"
    git pull origin "${BRANCH}"
    ok "Code updated"
fi

# --------------------------------------------------
# Ensure directories exist
# --------------------------------------------------
mkdir -p logs/caddy

# --------------------------------------------------
# Build new image (old containers keep running)
# --------------------------------------------------
log "Building production image..."
${COMPOSE} build
ok "Image built"

# --------------------------------------------------
# Restart with new image
# --------------------------------------------------
log "Restarting containers..."
${COMPOSE} up -d --remove-orphans
ok "Containers started"

# --------------------------------------------------
# Wait for DB health check
# --------------------------------------------------
log "Waiting for database..."
for i in $(seq 1 30); do
    if ${COMPOSE} exec -T db pg_isready -U porngurucam -d porngurucam > /dev/null 2>&1; then
        ok "Database ready"
        break
    fi
    if [ "$i" -eq 30 ]; then
        err "Database did not become ready in time"
        exit 1
    fi
    sleep 1
done

# --------------------------------------------------
# Run migrations
# --------------------------------------------------
log "Running migrations..."
${COMPOSE} exec -T app php artisan migrate --force
ok "Migrations done"

# --------------------------------------------------
# Optimize for production
# --------------------------------------------------
log "Optimizing caches..."
${COMPOSE} exec -T app php artisan optimize:clear
${COMPOSE} exec -T app php artisan optimize
${COMPOSE} exec -T app php artisan view:cache
${COMPOSE} exec -T app php artisan event:cache
ok "Caches built"

# --------------------------------------------------
# Generate sitemaps (non-fatal)
# --------------------------------------------------
log "Generating sitemaps..."
${COMPOSE} exec -T app php artisan sitemap:generate --static 2>/dev/null || warn "Sitemap generation skipped"

# --------------------------------------------------
# Health check
# --------------------------------------------------
log "Health check..."
sleep 3
HTTP_STATUS=$(curl -sk -o /dev/null -w "%{http_code}" https://localhost/up 2>/dev/null || \
              curl -s  -o /dev/null -w "%{http_code}" http://localhost/up  2>/dev/null || echo "000")

if [ "$HTTP_STATUS" = "200" ]; then
    ok "Health check passed (HTTP ${HTTP_STATUS})"
else
    warn "Health check returned HTTP ${HTTP_STATUS}"
    echo "  Check logs: docker compose -f ${COMPOSE_FILE} logs -f app"
fi

# --------------------------------------------------
# Status
# --------------------------------------------------
echo ""
${COMPOSE} ps
echo ""
ok "Deployment complete!"
echo ""
echo "  Site:  https://pornguru.cam"
echo "  Logs:  docker compose -f ${COMPOSE_FILE} logs -f app"
echo ""
