
export PROJECT_DIR = $(shell pwd)
export INFRA_DIR = .infra
export COMPOSE_PROJECT_NAME ?= regulation
export DOCKER_COMPOSE = docker compose -f .infra/docker/dev/docker-compose.yml


help: ## Показывает справку по Makefile
	@printf "\033[33m%s:\033[0m\n" 'Доступные команды'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

init: init/no-database data/migration ## Инициализация проекта

init/no-database: ## Инициализация проекта без БД
	$(DOCKER_COMPOSE) up -d

data/migration: ## Инициализация фикстур
	$(DOCKER_COMPOSE) exec -T app composer install

start: ## Запуск контейнеров
	$(DOCKER_COMPOSE) up -d

stop: ## Остановка контейнеров
	$(DOCKER_COMPOSE) stop