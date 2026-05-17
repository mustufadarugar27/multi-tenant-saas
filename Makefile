DC  = docker compose
APP = $(DC) exec app
NODE = $(DC) exec node

help:
	@echo ""
	@echo "Usage: make [command] [args]"
	@echo ""
	@echo "  setup                Full environment setup (up + migrate:fresh + seeders)"
	@echo "  up                   Start all containers in background"
	@echo "  down                 Stop and remove all containers"
	@echo "  logs                 Follow logs from all containers"
	@echo ""
	@echo "  artisan [cmd]        Run php artisan inside app container"
	@echo "  composer [cmd]       Run composer inside app container"
	@echo "  php [cmd]            Run php inside app container"
	@echo "  npm [cmd]            Run npm inside node container"
	@echo "  node [cmd]           Run node inside node container"
	@echo ""
	@echo "Examples:"
	@echo "  make artisan migrate"
	@echo "  make artisan tinker"
	@echo "  make composer require vendor/pkg"
	@echo "  make npm install"
	@echo ""

setup:
	$(DC) up -d
	@echo "Waiting for MySQL to be ready..."
	@until docker compose exec mysql mysqladmin ping -u root -proot --silent 2>/dev/null; do sleep 2; done
	$(APP) php artisan migrate:fresh
	$(APP) php artisan db:seed --class=PlanSeeder
	$(APP) php artisan db:seed --class=LangTranslationSeeder

up:
	$(DC) up -d

down:
	$(DC) down

logs:
	$(DC) logs -f

artisan:
	$(APP) php artisan $(filter-out $@,$(MAKECMDGOALS))

composer:
	$(APP) composer $(filter-out $@,$(MAKECMDGOALS))

php:
	$(APP) php $(filter-out $@,$(MAKECMDGOALS))

node:
	$(NODE) node $(filter-out $@,$(MAKECMDGOALS))

npm:
	$(NODE) npm $(filter-out $@,$(MAKECMDGOALS))

%:
	@:

.PHONY: help setup up down logs artisan composer php node npm
