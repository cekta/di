ci: update test
install:
	composer install
update:
	composer update
test: phpcs phpstan phpunit infection
phpcs:
	./vendor/bin/phpcs
phpstan:
	./vendor/bin/phpstan analyse --memory-limit=-1
phpunit:
	./vendor/bin/phpunit
infection:
	./vendor/bin/infection --show-mutations

docker-ci: 
	docker compose run --rm -it app ci
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
docker-shell:
	docker compose run -it --rm --entrypoint /bin/sh app 
docker-pages-build:
	docker compose run --rm -it pages build