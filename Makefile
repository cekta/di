install:
	composer install
test: phpcs phpstan phpunit infection clean
clean:
	rm tests/ExampleCompiled.php
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
docker:
	docker-compose up
docker-build-8.0:
	PHP_VERSION=8.0 docker-compose build
docker-build-8.1:
	PHP_VERSION=8.1 docker-compose build
docker-build-8.2:
	PHP_VERSION=8.2 docker-compose build