DC := docker-compose

help:
	@echo "SymReact\n"
	@echo "The list of commands for local development:\n"
	@echo "  build            	 		Builds the docker images for the docker-compose setup"
	@echo "  run              	 		Runs the containers"
	@echo "  stop              	 		Stop the containers"
	@echo "  lint             	 		Runs linter"
	@echo "  composer         	 		Runs composer install"
	@echo "  cc         	  	 		Runs cache clear"
	@echo "  test 			     		Runs tests"

build:
	"${DC}" up -d --build

run:
	"${DC}" up -d

stop:
	"${DC}" stop

lint:
	vendor/bin/php-cs-fixer fix src/ -vv
	vendor/bin/phpstan analyse

composer:
	composer install

cc:
	symfony console ca:cl

test:
	symfony php bin/phpunit
