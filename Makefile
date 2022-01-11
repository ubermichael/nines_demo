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
TWIGCS = ./vendor/bin/twigcs

# Misc Makefile stuff
.DEFAULT_GOAL = help
.PHONY:

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## -- Composer targets

install: composer.lock ## Installs vendors from composer.lock
	@$(COMPOSER) install

update: ## Updates vendors
	@$(COMPOSER) update

composer.lock: update

## -- Console targets

cc: ## Clear the symfony cache
	@$(CONSOLE) cache:clear
	@$(CONSOLE) cache:warmup

purge: ## Remove cache and log files
	@rm -rf var/cache/*/*
	@rm var/log/*
	@$(CONSOLE) cache:warmup

assets: ## Link assets into /public
	@$(CONSOLE) assets:install --symlink

reset: ## Drop the database and recreate it with fixtures
	@$(CONSOLE) doctrine:cache:clear-metadata -q
	@$(CONSOLE) doctrine:database:drop --if-exists --force -q
	@$(CONSOLE) doctrine:database:create -q
	@$(CONSOLE) doctrine:schema:create -q
	@$(CONSOLE) doctrine:schema:validate -q
	@$(CONSOLE) doctrine:fixtures:load -q --no-interaction --group=test

## -- Yarn assets

yarn.lock: package.json
	@$(YARN) upgrade

yarn: ## Install yarn assets
	@$(YARN) install

## -- Test targets

testdb: ## Create a test database and load the fixtures in it
	@$(CONSOLE) --env=test doctrine:cache:clear-metadata -q
	@$(CONSOLE) --env=test doctrine:database:drop --if-exists --force -q
	@$(CONSOLE) --env=test doctrine:database:create -q
	@$(CONSOLE) --env=test doctrine:schema:create -q
	@$(CONSOLE) --env=test doctrine:schema:validate -q
	@$(CONSOLE) --env=test doctrine:fixtures:load -q --no-interaction --group=test

testclean: ## Clean up any test files
	@rm -rf data/test

test: testclean testdb ## Run all tests
	@$(PHPUNIT) --stop-on-error --stop-on-failure

testapp: testclean testdb
	@$(PHPUNIT) --stop-on-error --stop-on-failure ./tests

## -- Coding standards targets

lint-all: stan.cc stan lint twiglint twigcs yamllint

stan: ## Run static analysis
	@$(PHPSTAN) analyze

stan.cc: ## Clear the static analysis cache
	@$(PHPSTAN) clear-result-cache

baseline: ## Generate a new phpstan baseline file
	@$(PHPSTAN) analyze --generate-baseline

lint: ## Check the code against the CS rules
	@$(PHPCSF) fix --dry-run -v

fix: ## Fix the code with the CS rules
	@$(PHPCSF) fix

fix-all: ## Ignore the CS cache and fix the code with the CS rules
	@$(PHPCSF) fix --using-cache=no

twiglint: ## Check the twig templates for syntax errors
	@$(CONSOLE) lint:twig templates lib/Nines

twigcs: ## Check the twig templates against the coding standards
	@$(TWIGCS) templates lib/Nines/*/templates

yamllint:
	@$(CONSOLE) lint:yaml templates lib/Nines
