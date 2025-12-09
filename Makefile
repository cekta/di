shell:
	docker compose run -it --rm --entrypoint /bin/sh app 
shell-docs:
	docker compose run -it --rm --entrypoint /bin/sh pages
docs:
	docker compose up pages
docs-build:
	docker compose run --rm pages build
run:
	docker compose run --rm -it app
test-8.2:
	PHP_VERSION=8.2 docker compose build
	docker compose run --rm -it app
test-8.3:
	PHP_VERSION=8.3 docker compose build
	docker compose run --rm -it app
test-8.4:
	PHP_VERSION=8.4 docker compose build
	docker compose run --rm -it app
test-8.5:
	PHP_VERSION=8.5 docker compose build
	docker compose run --rm -it app