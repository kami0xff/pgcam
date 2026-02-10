# PornGuru Cam

Live cam model aggregator with multilingual SEO support, powered by Laravel 12 and FrankenPHP.

## Features

- **Live Model Listings** - Real-time cam model data from Stripchat
- **Multilingual SEO** - 16+ languages with translated tags, countries, and descriptions
- **Smart Filtering** - By niche (girls, guys, couples, trans), tags, and countries
- **Online Schedule Heatmap** - Shows when models are typically online
- **Room Goals Tracking** - Track and display completed room goals
- **AI-Generated Content** - Model descriptions and FAQs via Anthropic Claude
- **Automatic HTTPS** - Let's Encrypt via Caddy/FrankenPHP

## Tech Stack

- **Backend**: Laravel 12, PHP 8.3
- **Server**: FrankenPHP (Caddy + PHP in one binary)
- **Databases**: PostgreSQL (app data), External PostgreSQL (cam models)
- **Cache/Sessions**: Redis
- **Frontend**: Vite, Blade, Vanilla CSS

## Development Setup

### Prerequisites

- Docker & Docker Compose
- Git

### Quick Start

```bash
# Clone the repository
git clone <repo-url> porngurucam
cd porngurucam

# Copy environment file
cp .env.example .env

# Start development environment
docker compose -f docker-compose.dev.yml up -d

# Install dependencies
docker compose -f docker-compose.dev.yml exec app composer install
docker compose -f docker-compose.dev.yml exec app npm install

# Generate app key
docker compose -f docker-compose.dev.yml exec app php artisan key:generate

# Run migrations
docker compose -f docker-compose.dev.yml exec app php artisan migrate

# Seed initial data
docker compose -f docker-compose.dev.yml exec app php artisan run:all --data
```

Visit: http://localhost:8787

## Production Deployment

### 1. Server Requirements

- Docker & Docker Compose
- Domain pointing to server IP
- Ports 80, 443 open

### 2. Setup

```bash
# Clone to production server
git clone <repo-url> /opt/porngurucam
cd /opt/porngurucam

# Create production environment
cp .env.production.example .env.production

# Edit .env.production with your values:
# - Generate APP_KEY: php artisan key:generate --show
# - Set CAM_DB_* credentials
# - Set DB_PASSWORD
# - Set ANTHROPIC_API_KEY
nano .env.production

# Build and start
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Optimize for production
docker compose -f docker-compose.prod.yml exec app php artisan optimize
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# Generate initial data
docker compose -f docker-compose.prod.yml exec app php artisan run:all --full
```

### 3. SSL Certificates

Caddy/FrankenPHP automatically obtains Let's Encrypt certificates when:
- Domain resolves to the server
- Ports 80/443 are accessible
- `SERVER_NAME` is set in docker-compose.prod.yml

### 4. Cron Jobs

Add to crontab:
```bash
* * * * * docker compose -f /opt/porngurucam/docker-compose.prod.yml exec -T app php artisan schedule:run >> /dev/null 2>&1
```

### 5. Background Translation Worker (Optional)

Run in a separate screen/tmux session:
```bash
docker compose -f docker-compose.prod.yml exec app php artisan translate:worker --rate=10
```

## Commands Reference

### Data Collection
```bash
# Run all data tasks
php artisan run:all --data

# Full run with AI descriptions
php artisan run:all --full --desc-limit=50

# Individual commands
php artisan sync:model-goals --limit=5000
php artisan heatmap:record
php artisan heatmap:aggregate
php artisan tags:update-counts --countries
```

### Translations
```bash
# Background worker (recommended)
php artisan translate:worker --rate=10

# One-time tag/country translation
php artisan translate:all --priority

# Profile translations
php artisan translate:profiles --locale=es --limit=50
```

### SEO
```bash
# Generate sitemaps
php artisan sitemap:generate --static

# Generate AI descriptions
php artisan seo:generate-model-descriptions --limit=50
```

## Monitoring

### Health Check
```
GET /up - Returns 200 if healthy
```

### Logs
```bash
# Application logs
docker compose -f docker-compose.prod.yml logs -f app

# Laravel logs
docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log
```

## Architecture

```
porngurucam/
├── app/
│   ├── Console/Commands/    # Artisan commands
│   ├── Http/Controllers/    # Web controllers
│   ├── Models/              # Eloquent models
│   └── Enums/               # PHP enums (StripchatTag)
├── resources/
│   ├── views/               # Blade templates
│   │   ├── components/      # Blade components
│   │   └── cam-models/      # Model pages
│   └── css/                 # Stylesheets
├── config/
│   └── locales.php          # Supported languages
├── docker/                  # Docker configs
├── docker-compose.dev.yml   # Development
├── docker-compose.prod.yml  # Production
├── Dockerfile.frankenphp    # Production image
└── Caddyfile               # Caddy/FrankenPHP config
```

## Database Schema

### Main Database (pgsql)
- `tags` - Cam categories (from StripchatTag enum)
- `tag_translations` - Translated tag names/slugs
- `countries` - Model countries
- `country_translations` - Translated country names
- `model_descriptions` - AI-generated descriptions
- `model_faqs` - AI-generated FAQs
- `model_goals` - Room goal history
- `model_heatmaps` - Online schedule percentages
- `model_online_snapshots` - Raw online tracking data

### External Database (cam)
- `cam_models` - Live model data (read-only)

## License

Proprietary - All rights reserved.
