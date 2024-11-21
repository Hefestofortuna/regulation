export PROJECT_DIR = $(shell pwd)
export INFRA_DIR = .infra
export COMPOSE_PROJECT_NAME ?= regulation
export DOCKER_COMPOSE = docker compose -f .infra/docker/dev/docker-compose.yml
export DB_CONNECTION := postgres
export DB_HOST := database
export DB_PORT := 5432
export DB_DATABASE := database
export DB_USERNAME := root
export DB_PASSWORD := roottoor
export SECRET_KEY := aHR0cHM6Ly9naXRodWIuY29tL2xhcmF2ZWwvbHVtZW4=
export APP_DEBUG := true

help: ## Показывает справку по Makefile
	@printf "\033[33m%s:\033[0m\n" 'Доступные команды'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

init: start init/no-database init/env init/composer data/migration ## Инициализация проекта

init/env: ## Генерация .env
	$(DOCKER_COMPOSE) exec -T app cp .env.example .env
	$(DOCKER_COMPOSE) exec -T app sh -c 'sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=$(DB_CONNECTION)|" .env && \
    sed -i "s|DB_HOST=.*|DB_HOST=$(DB_HOST)|" .env && \
    sed -i "s|DB_PORT=.*|DB_PORT=$(DB_PORT)|" .env && \
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$(DB_DATABASE)|" .env && \
    sed -i "s|SECRET_KEY=.*|SECRET_KEY=$(SECRET_KEY)|" .env && \
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$(DB_USERNAME)|" .env && \
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$(DB_USERNAME)|" .env && \
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=$(APP_DEBUG)|" .env && \
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$(DB_PASSWORD)|" .env'

init/composer: ## Инициализация composer
	$(DOCKER_COMPOSE) exec -T app composer install

init/no-database: ## Инициализация проекта без БД
	#$(DOCKER_COMPOSE) up -d

data/migration: ## Инициализация фикстур
	$(DOCKER_COMPOSE) exec -T app

start: ## Запуск контейнеров
	$(DOCKER_COMPOSE) up -d

stop: ## Остановка контейнеров
	$(DOCKER_COMPOSE) stop

flush/all: ## Чистка контейнеров
	$(DOCKER_COMPOSE) down -v

cs/fix: ## Обновление код-стайла
	$(DOCKER_COMPOSE) exec -T app composer fix