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
	XDEBUG_MODE=coverage ./vendor/bin/infection -s --min-msi=100 --min-covered-msi=100 -v
