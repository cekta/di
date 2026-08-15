.PHONY: dev
dev:
	docker compose up -d --remove-orphans

.PHONY: shell
shell: dev
	docker compose exec app sh

.PHONY: shell-docs
shell-docs:
	docker compose run -it --rm --entrypoint /bin/sh pages

.PHONY: docs-build
docs-build:
	docker compose run --rm pages build

.PHONY: run
run:
	docker compose run --rm -it app composer test

.PHONY: test-8.2
test-8.2:
	docker compose down
	PHP_VERSION=8.2 docker compose build
	make dev
	docker compose exec app composer update
	docker compose exec app composer test

.PHONY: test-8.3
test-8.3:
	docker compose down
	PHP_VERSION=8.3 docker compose build
	make dev
	docker compose exec app composer update
	docker compose exec app composer test

.PHONY: test-8.4
test-8.4:
	docker compose down
	PHP_VERSION=8.4 docker compose build
	make dev
	docker compose exec app composer update
	docker compose exec app composer test

.PHONY: test-8.5
test-8.5:
	docker compose down
	PHP_VERSION=8.5 docker compose build
	make dev
	docker compose exec app composer update
	docker compose exec app composer test