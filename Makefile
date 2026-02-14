# ==============================================
# pornguru.cam - Dev & Prod Commands
# ==============================================
# Usage:
#   make dev        - Start development environment
#   make prod       - Deploy production
#   make logs       - Tail production logs
#   make help       - Show all commands
# ==============================================

.PHONY: help dev dev-up dev-down dev-logs dev-shell prod prod-up prod-down prod-logs prod-shell migrate fresh test build

# Default
help:
	@echo ""
	@echo "  Development"
	@echo "  ─────────────────────────────────────"
	@echo "  make dev          Start dev stack"
	@echo "  make dev-down     Stop dev stack"
	@echo "  make dev-logs     Tail dev logs"
	@echo "  make dev-shell    Shell into dev app"
	@echo "  make migrate      Run migrations (dev)"
	@echo "  make fresh        Fresh migrate + seed (dev)"
	@echo "  make test         Run tests"
	@echo ""
	@echo "  Production"
	@echo "  ─────────────────────────────────────"
	@echo "  make prod         Full production deploy"
	@echo "  make prod-up      Start prod (no rebuild)"
	@echo "  make prod-down    Stop prod stack"
	@echo "  make prod-logs    Tail prod logs"
	@echo "  make prod-shell   Shell into prod app"
	@echo "  make build        Build prod image only"
	@echo ""
	@echo "  Git Workflow"
	@echo "  ─────────────────────────────────────"
	@echo "  make feature F=name  Create feature branch"
	@echo "  make finish          Merge current feature → dev"
	@echo "  make release         Merge dev → main (deploy)"
	@echo ""

# ==============================================
# Development
# ==============================================

dev:
	docker compose -f docker-compose.dev.yml up -d
	@echo ""
	@echo "  Dev running at http://localhost:8787"
	@echo "  Vite HMR at   http://localhost:5173"
	@echo ""

dev-down:
	docker compose -f docker-compose.dev.yml down

dev-logs:
	docker compose -f docker-compose.dev.yml logs -f app

dev-shell:
	docker compose -f docker-compose.dev.yml exec app sh

migrate:
	docker compose -f docker-compose.dev.yml exec app php artisan migrate

fresh:
	docker compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed

test:
	docker compose -f docker-compose.dev.yml exec app php artisan test

# ==============================================
# Production
# ==============================================

prod:
	./deploy.sh

build:
	docker compose -f docker-compose.prod.yml build

prod-up:
	docker compose -f docker-compose.prod.yml up -d

prod-down:
	docker compose -f docker-compose.prod.yml down

prod-logs:
	docker compose -f docker-compose.prod.yml logs -f app

prod-shell:
	docker compose -f docker-compose.prod.yml exec app sh

# ==============================================
# Git Workflow Helpers
# ==============================================

# Create a feature branch off dev
# Usage: make feature F=stream-quality-fix
feature:
ifndef F
	@echo "Usage: make feature F=my-feature-name"
	@exit 1
endif
	git checkout dev
	git pull origin dev
	git checkout -b feature/$(F)
	@echo "Created feature/$(F) from dev"

# Merge current feature branch back into dev
finish:
	$(eval BRANCH := $(shell git branch --show-current))
	@if echo "$(BRANCH)" | grep -q "^feature/"; then \
		git checkout dev && \
		git pull origin dev && \
		git merge $(BRANCH) && \
		git push origin dev && \
		git branch -d $(BRANCH) && \
		echo "Merged $(BRANCH) → dev"; \
	else \
		echo "Not on a feature branch (current: $(BRANCH))"; \
		exit 1; \
	fi

# Merge dev into main for production release
release:
	@echo "Merging dev → main..."
	git checkout main
	git pull origin main
	git merge dev
	git push origin main
	git checkout dev
	@echo ""
	@echo "Pushed to main. CI/CD will deploy automatically."
	@echo "Or run: make prod"
	@echo ""
