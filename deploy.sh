#!/bin/bash
# ==============================================
# Production Deployment Script for pornguru.cam
# ==============================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Starting deployment...${NC}"

# Check if .env.production exists
if [ ! -f ".env.production" ]; then
    echo -e "${RED}Error: .env.production not found!${NC}"
    echo "Copy .env.production.example to .env.production and configure it."
    exit 1
fi

# Pull latest changes (if git repo)
if [ -d ".git" ]; then
    echo -e "${YELLOW}📥 Pulling latest changes...${NC}"
    git pull origin main
fi

# Build production image
echo -e "${YELLOW}🔨 Building production Docker image...${NC}"
docker compose -f docker-compose.prod.yml build --no-cache

# Stop existing containers
echo -e "${YELLOW}🛑 Stopping existing containers...${NC}"
docker compose -f docker-compose.prod.yml down

# Start new containers
echo -e "${YELLOW}🚀 Starting containers...${NC}"
docker compose -f docker-compose.prod.yml up -d

# Wait for database to be ready
echo -e "${YELLOW}⏳ Waiting for database...${NC}"
sleep 10

# Run migrations
echo -e "${YELLOW}📊 Running migrations...${NC}"
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

# Clear and rebuild caches
echo -e "${YELLOW}🗄️ Optimizing for production...${NC}"
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan event:cache

# Generate sitemaps
echo -e "${YELLOW}🗺️ Generating sitemaps...${NC}"
docker compose -f docker-compose.prod.yml exec -T app php artisan sitemap:generate --static

# Health check
echo -e "${YELLOW}🏥 Running health check...${NC}"
sleep 5
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/up || echo "000")

if [ "$HTTP_STATUS" = "200" ]; then
    echo -e "${GREEN}✅ Deployment successful! Health check passed.${NC}"
else
    echo -e "${RED}⚠️ Health check returned status: $HTTP_STATUS${NC}"
    echo "Check logs with: docker compose -f docker-compose.prod.yml logs -f app"
fi

# Show container status
echo -e "${YELLOW}📊 Container status:${NC}"
docker compose -f docker-compose.prod.yml ps

echo -e "${GREEN}🎉 Deployment complete!${NC}"
echo ""
echo "Next steps:"
echo "  1. Verify site is working: https://pornguru.cam"
echo "  2. Set up cron for scheduler:"
echo "     * * * * * cd $(pwd) && docker compose -f docker-compose.prod.yml exec -T app php artisan schedule:run"
echo "  3. (Optional) Start translation worker in screen/tmux:"
echo "     docker compose -f docker-compose.prod.yml exec app php artisan translate:worker --rate=10"
