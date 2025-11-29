default: update
	mdbook serve -n 0.0.0.0
ci: update test
install:
	composer install
test: phpcs phpstan phpunit infection
update:
	composer update
phpcs:
	./vendor/bin/phpcs
phpstan:
	./vendor/bin/phpstan analyse --memory-limit=-1
phpunit:
	./vendor/bin/phpunit
infection:
	./vendor/bin/infection --show-mutations
pages-build:
	mdbook build

docker-ci: up
	docker compose run app ci
docker-build-8.0:
	PHP_VERSION=8.0 docker compose build
docker-build-8.1:
	PHP_VERSION=8.1 docker compose build
docker-build-8.2:
	PHP_VERSION=8.2 docker compose build
docker-build-8.3:
	PHP_VERSION=8.3 docker compose build
docker-build-8.4:
	PHP_VERSION=8.4 docker compose build
docker-run-8.0: docker-build-8.0 docker-ci
docker-run-8.1: docker-build-8.1 docker-ci
docker-run-8.2: docker-build-8.2 docker-ci
docker-run-8.3: docker-build-8.3 docker-ci
docker-run-8.4: docker-build-8.4 docker-ci
up:
	docker compose up -d --remove-orphans
docker-shell: up
	docker compose exec -it app sh
docker-pages-build:
	docker compose run --rm app pages-build