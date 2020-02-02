install:
	composer install
test: phpcs phpmd phpstan phpinsights phpunit infection
script: phpcs phpmd phpstan phpinsights phpunit_with_clover infection
before_script: codeclimate_before scrutinizer_install
codeclimate_install:
	curl -L https://codeclimate.com/downloads/test-reporter/test-reporter-latest-linux-amd64 > ./cc-test-reporter
	chmod +x ./cc-test-reporter
codeclimate_before: codeclimate_install
	./cc-test-reporter before-build
scrutinizer_install:
	wget https://scrutinizer-ci.com/ocular.phar
after_script:
	./cc-test-reporter after-build --exit-code ${TRAVIS_TEST_RESULT} clover.xml
	php ocular.phar code-coverage:upload --format=php-clover clover.xml
update:
	composer update
phpcs:
	./vendor/bin/phpcs
phpstan:
	./vendor/bin/phpstan analyse
phpinsights:
	./vendor/bin/phpinsights --min-quality=100 --min-architecture=100 --min-style=100 --no-interaction -vvv
phpunit:
	./vendor/bin/phpunit
phpunit_with_clover:
	./vendor/bin/phpunit --coverage-clover clover.xml
phpmd:
	./vendor/bin/phpmd src ansi phpmd.ruleset.xml
infection:
	./vendor/bin/infection -s --min-msi=100 --min-covered-msi=100
page:
	docker run \
		--rm \
		-it \
		-v "$$PWD/docs":/srv/jekyll \
		-v "$$PWD/bundle":/usr/local/bundle \
		-p 4000:4000 \
		jekyll/jekyll jekyll serve
