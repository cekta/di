XDEBUG_MODE ?=coverage
install:
	composer install
test: phpcs phpstan phpunit infection
script: phpcs phpstan phpunit_with_clover infection
before_script:
	curl -L https://codeclimate.com/downloads/test-reporter/test-reporter-latest-linux-amd64 > ./cc-test-reporter
	chmod +x ./cc-test-reporter
	./cc-test-reporter before-build
after_script:
	./cc-test-reporter after-build --exit-code ${TRAVIS_TEST_RESULT} clover.xml
	echo ${CC_TEST_REPORTER_ID}
update:
	composer update
phpcs:
	./vendor/bin/phpcs
phpstan:
	./vendor/bin/phpstan analyse --memory-limit=-1
phpunit:
	./vendor/bin/phpunit
phpunit_with_clover:
	XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-clover clover.xml
infection:
	XDEBUG_MODE=coverage ./vendor/bin/infection -s --min-msi=100 --min-covered-msi=100 -v
