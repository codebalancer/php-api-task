.PHONY: start stop init build tests tests-functional

start:
	docker-compose up -d

stop:
	docker-compose stop

init:
	docker-compose build
	docker-compose up -d
	docker-compose exec php composer install
	docker-compose exec php php bin/console doctrine:database:create
	docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
	docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction

build:
	build/build.sh

tests:
	docker-compose exec php php vendor/bin/simple-phpunit --exclude-group functional

tests-functional:
	docker-compose exec php rm var/test.sqlite -f
	docker-compose exec php php bin/console doctrine:schema:create --env=test
#	docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction --env=test
#	docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction --env=test
	docker-compose exec php php vendor/bin/simple-phpunit --group functional


