install:
	composer install
test: phpcs phpmd phpstan phpinsights phpunit infection
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
