install:
	composer install
test: phpcs phpmd phpstan phpinsights phpunit infection
script: phpcs phpmd phpstan phpinsights phpunit_with_clover infection
before_script:
	curl -L https://codeclimate.com/downloads/test-reporter/test-reporter-latest-linux-amd64 > ./cc-test-reporter
	chmod +x ./cc-test-reporter
	./cc-test-reporter before-build
after_script:
	./cc-test-reporter after-build --exit-code 0 -r f398f5a0839235aed75d94c5f6dc7dcb3de22e95f5104adbabcabd480bc819bd clover.xml
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
