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
	phpdbg -qrr ./vendor/bin/infection
