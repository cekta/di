# Project: cekta/di

PSR-11 контейнер с компиляцией на этапе сборки.
Все зависимости разрешаются при компиляции, генерируется чистый PHP-код без рефлексии в рантайме.

## Основной поток

1. `ContainerBuilder` получает конфигурацию (entries, params, aliases, singletons, factories)
2. `DependencyMap` рекурсивно разрешает зависимости через `ReflectionService`
3. `Template` генерирует PHP-класс контейнера из `template/container.compiler.php`
4. Результат — чистый PHP-файл, реализующий `Psr\Container\ContainerInterface`

## Окружение разработки

Сначала запускаем среду:

```
make dev
```

Рабочая оболочка:

```
make shell
```

Все команды (`php`, `composer` и т.д.) запускаются **только** внутри `make shell`.

## CI / Тесты

Полный пайплайн:

| Target        | Команда                                    |
| ------------- | ------------------------------------------ |
| `make ci`     | `docker compose run --rm app composer test` |

Команда `composer test` запускает: `phpcs` → `phpstan` → `phpunit` → `infection`.

Тестирование на конкретных PHP-версиях:

| Target        | Описание         |
| ------------- | ---------------- |
| `make test-8.2` | PHP 8.2        |
| `make test-8.3` | PHP 8.3        |
| `make test-8.4` | PHP 8.4        |
| `make test-8.5` | PHP 8.5        |

Документация (mdbook):

| Target          | Команда                     |
| --------------- | --------------------------- |
| `make shell-docs` | Оболочка для bookbuilder  |
| `make docs-build` | Сборка документации        |

## Boundaries

- **Never commit:** секреты, `.env`, файлы в `runtime/`.
- **Never commit** код, не проходящий `make ci` / `composer test`.
- **Never run** инструменты напрямую на хосте.
- **Never add** runtime-зависимости в `composer.json` (это библиотека).

## Conventions

- PHP 8.2+ минимум.
- Все свойства — с явными типами.
- [PER Coding style для всех *.php файлов.](https://www.php-fig.org/per/coding-style/).
- Написание документации осуществляется с помощью [mdbook](https://rust-lang.github.io/mdBook/).

## Project Structure

```
.github/workflows/              # Директория с конфигурацией CI.
book/                           # Директория с книгой mdbook.
docs/                           # Директория документации на mdbook
template/                       # Директория шаблонов для контейнеров, read/write.
tests/                          # Директория автотестов проекта
src/                            # Директория исходный код проекта
├── ContainerBuilder.php        # Точка входа, readonly конфиг
├── DependencyMap.php           # Разрешение графа зависимостей, детект циклов
├── DependencyMap/
│   ├── Dependency.php          # Абстрактная база для всех типов зависимостей
│   ├── Dependency/
│   │   ├── Alias.php           # Маппинг interface -> implementation
│   │   ├── Autowiring.php      # Стандартная автозависимость (per-request)
│   │   ├── AutowiringShared.php # Синглтон / shared-инстанс
│   │   ├── Container.php       # Точка входа контейнера
│   │   └── Param.php           # Скалярный параметр
│   └── Parameter.php           # DTO для параметров конструктора
├── Exception/
│   ├── CircularDependency.php  # Циклическая зависимость
│   ├── IntersectConfiguration.php
│   ├── NotFound.php            # Рантайм: зависимость не найдена
│   ├── NotFoundOnCompile.php   # Компиляция: класс не найден
│   └── NotInstantiable.php     # Абстрактный класс / интерфейс без алиаса
├── FQCN.php                    # Парсинг полностью определённого имени класса
├── Lazy.php                    # Интерфейс для отложенного разрешения
├── Lazy/
│   ├── Closure.php             # Отложенное значение из callable
│   └── Container.php           # Отложенный доступ к контейнеру
├── ReflectionService.php       # Рефлексия для извлечения параметров конструктора
└── Template.php                # Рендер PHP-шаблона с параметрами
book.toml                       # Конфигурация mdbook
composer.json                   # Конфигурация composer.json
Makefile                        # Команды запускаемые на хостовой системе
```
