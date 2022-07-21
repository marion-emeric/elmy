DOCKER_COMPOSE = docker compose
PHP_CONTAINER = "php"

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc

## -- Docker PHP Cli Makefile --
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

up: ## Build the images and start the containers
	@$(DOCKER_COMPOSE) up -d --build --force-recreate

down: ## Stop the docker hub
	@$(DOCKER_COMPOSE) down --remove-orphans

## -- Composer --
composer: ## Run composer; use c= to pass arguments example c="req my/package"
	@$(eval c ?=)
	@$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) composer $(c)

## -- PHP --
php: ## Run php command line; use c= to pass arguments example c="-v"
	@$(eval c ?=)
	@$(DOCKER_COMPOSE) exec $(PHP_CONTAINER) php $(c)

sh:
	@$(DOCKER_COMPOSE) exec -ti $(PHP_CONTAINER) sh
