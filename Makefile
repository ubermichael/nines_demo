# Executables
COMPOSER = /usr/local/bin/composer
SYMFONY = /usr/local/bin/symfony
YARN = /usr/local/bin/yarn
PHP = /usr/local/bin/php
BREW = /usr/local/bin/brew

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

# Silence output slightly
.SILENT:

# Useful URLs
LOCAL=http://localhost/newnines/public

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## -- General targets
open: ## Open the project home page in a browser
	open $(LOCAL)

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
	rm -rf var/cache/*/*
	rm var/log/*
	$(CONSOLE) cache:warmup

assets: ## Link assets into /public
	$(CONSOLE) assets:install --symlink

reset: ## Drop the database and recreate it with fixtures
	$(CONSOLE) doctrine:cache:clear-metadata --quiet
	$(CONSOLE) doctrine:database:drop --if-exists --force --quiet
	$(CONSOLE) doctrine:database:create --quiet
	$(CONSOLE) doctrine:schema:create --quiet
	$(CONSOLE) doctrine:schema:validate --quiet
	$(CONSOLE) doctrine:fixtures:load --quiet --no-interaction --group=test

## -- Yarn assets

yarn.lock: package.json
	$(YARN) upgrade

yarn: ## Install yarn assets
	$(YARN) install

## -- Container debug targets

dump-params: ## List all of the nines container parameters
	$(CONSOLE) debug:container --parameters | grep '^\s*nines'

dump-env: ## Show all environment variables in the container
	$(CONSOLE) debug:container --env-vars

dump-autowire: ## Show autowireable services
	$(CONSOLE) debug:autowiring nines --all

## -- Useful development services

mailhog-start: ## Start the email catcher
	$(BREW) services start mailhog
	open http://localhost:8025

mailhog-stop: ## Stop the email catcher
	$(BREW) services stop mailhog

## -- Test targets

testdb: ## Create a test database and load the fixtures in it
	$(CONSOLE) --env=test doctrine:cache:clear-metadata --quiet
	$(CONSOLE) --env=test doctrine:database:drop --if-exists --force --quiet
	$(CONSOLE) --env=test doctrine:database:create --quiet
	$(CONSOLE) --env=test doctrine:schema:create --quiet
	$(CONSOLE) --env=test doctrine:schema:validate --quiet
	$(CONSOLE) --env=test doctrine:fixtures:load --quiet --no-interaction --group=test

testclean: ## Clean up any test files
	rm -rf data/test

test: testclean testdb ## Run all tests. Use optional path=/path/to/tests to limit target
	$(PHPUNIT) --stop-on-error --stop-on-failure $(path)

testcover: testclean testdb ## Generate a test cover report
	$(PHP) -d zend_extension=xdebug.so -d xdebug.mode=coverage $(PHPUNIT) -c phpunit.coverage.xml $(path)
	open $(LOCAL)/dev/coverage/index.html

## -- Coding standards targets

lint-all: stan.cc stan lint twiglint twigcs yamllint

symlint: ## Run the symfony linting checks
	$(SYMFONY) security:check --quiet
	$(CONSOLE) lint:yaml --quiet config --parse-tags
	$(CONSOLE) lint:twig --quiet templates
	$(CONSOLE) lint:container --quiet
	$(CONSOLE) doctrine:schema:validate --quiet --skip-sync -vvv --no-interaction

stan: ## Run static analysis
	$(PHPSTAN) analyze

stan.cc: ## Clear the static analysis cache
	$(PHPSTAN) clear-result-cache

baseline: ## Generate a new phpstan baseline file
	$(PHPSTAN) analyze --generate-baseline

lint: ## Check the code against the CS rules
	$(PHPCSF) fix --dry-run -v

fix: ## Fix the code with the CS rules
	$(PHPCSF) fix

fix-all: ## Ignore the CS cache and fix the code with the CS rules
	$(PHPCSF) fix --using-cache=no

twiglint: ## Check the twig templates for syntax errors
	$(CONSOLE) lint:twig templates lib/Nines

twigcs: ## Check the twig templates against the coding standards
	$(TWIGCS) templates lib/Nines/*/templates

yamllint:
	$(CONSOLE) lint:yaml templates lib/Nines
