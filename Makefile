# Executables
COMPOSER = /usr/local/bin/composer
SYMFONY = /usr/local/bin/symfony
YARN = /usr/local/bin/yarn
PHP = /usr/local/bin/php

# Aliases
CONSOLE = $(PHP) bin/console

# Vendor executables
PHPUNIT = ./vendor/bin/phpunit
PHPSTAN = ./vendor/bin/phpstan
PHPCSF = ./vendor/bin/php-cs-fixer

# Misc Makefile stuff
.DEFAULT_GOAL = help
.PHONY:

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## -- Composer targets

install: composer.lock ## Installs vendors from composer.lock
	$(COMPOSER) install

update: ## Updates vendors
	$(COMPOSER) update

composer.lock: update

## -- Console targets

cc: ## Clear the symfony cache
	$(CONSOLE) cache:clear
	$(CONSOLE) cache:warmup

purge: ## Remove cache and log files
	@rm -rf var/cache/*/*
	@rm var/log/*
	$(CONSOLE) cache:warmup

assets: ## Link assets into /public
	$(CONSOLE) assets:install --symlink

reset: ## Drop the database and recreate it with fixtures
	$(CONSOLE) doctrine:cache:clear-metadata
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:schema:drop --force
	$(CONSOLE) doctrine:schema:create
	$(CONSOLE) doctrine:schema:validate
	$(CONSOLE) doctrine:fixtures:load --no-interaction

## -- Yarn assets

yarn.lock: package.json
	$(YARN) upgrade

yarn: ## Install yarn assets
	$(YARN) install

## -- Test targets

test: ## Run the tests
	$(PHPUNIT) --stop-on-error --stop-on-failure

## -- Coding standards targets

stan: ## Run static analysis
	$(PHPSTAN) analyze

baseline: ## Generate a new phpstan baseline file
	$(PHPSTAN) analyze --generate-baseline

lint: ## Check the code against the CS rules
	$(PHPCSF) fix --dry-run -v

fix: ## Fix the code with the CS rules
	$(PHPCSF) fix

fix-all: ## Ignore the CS cache and fix the code with the CS rules
	$(PHPCSF) fix --using-cache=no

twiglint:
	$(CONSOLE) lint:twig config lib/Nines

yamllint:
	$(CONSOLE) lint:yaml templates lib/Nines
