up:
	docker-compose up -d
	docker-compose exec -d php symfony server:start --no-tls -d
	docker-compose exec -d php yarn watch

down:
	docker-compose down

rebuild:
	docker-compose down -v --remove-orphans
	docker-compose rm -vsf
	docker-compose up -d --build

test:
	docker-compose exec php composer test

db-build:
	docker-compose exec php ./bin/console doctrine:database:drop --force --if-exists
	docker-compose exec php ./bin/console doctrine:database:create
	docker-compose exec php ./bin/console doctrine:migrations:migrate -n
	docker-compose exec php ./bin/console app:create-account user@kiwi.nl user user
	docker-compose exec php ./bin/console app:create-account --admin admin@kiwi.nl admin admin

