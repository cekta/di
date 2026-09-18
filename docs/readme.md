# Контейнер зависимостей для PHP

Реализация для [PSR-11](https://www.php-fig.org/psr/psr-11/).

## 🚀 Как это работает

### 1. Создаем конфигурацию вашего проекта

**src/Project.php**

```php
<?php
namespace App;

class Project extends \Cekta\DI\AbstractProject
{
    public function __construct(public readonly array $env = getenv())
    {
        parent::__construct(
            filename: __DIR__ . '/../runtime/Container.php',
            fqcn: 'App\Runtime\Container',
            params: [
                // ваши параметры
                \PDO::class . '$dsn' => $env['DB_DSN'] ?? 'sqlite:' . __DIR__ . '/../mydb.sqlite',
            ],
        );
    }

    public function definition(): array
    {
        return [
            'entries' => [
                \PDO::class,
            ],
            'alias' => [],
            'singletons' => [],
            'factories' => [],
        ];
    }
}
```

В будущем вся настройка внедрения зависимостей осуществляется здесь.

### 2. Generate — создаём контейнер.

**bin/generate.php** — скрипт для генерации контейнера:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$project = new App\Project();
$generator = new Cekta\DI\ContainerGenerator();
file_put_contents($project->filename, $generator->generate($project));
echo "{$project->filename} was generated" . PHP_EOL;
```

Запускаем когда требуется перегенерация:

```shell
php bin/generate.php
```

### 3. Usage — используем контейнер

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$project = new App\Project();
$factory = new Cekta\DI\ContainerFactory();
$container = $factory->create($project);
$pdo = $container->get(\PDO::class); // можно доставать любой entries
```

## ✨ Преимущества

- **Скорость** — нет накладных расходов в рантайме: контейнер готов сразу.
- **Оптимизация** — работает напрямую с opcache, не требует кэширования.
- **Безопасность** — ошибки конфигурации обнаруживаются на этапе сборки.
- **Гибкость** — генерируйте код при деплое, в CI/CD или при первом запуске, как вам удобно.

**Поддерживаемые возможности PHP:**

- **Builtin-типы** — `__construct(string $username)`;
- **Пользовательские классы и интерфейсы** — `__construct(\PDO $db)`;
- **Union types** — `__construct(Foo|Bar $argument)`;
- **Intersection types** — `__construct(Foo&Bar $arg)`;
- **DNF types** — `__construct((Foo&Bar)|Baz $arg)`;
- **Nullable-аргументы** — поддерживаются;
- **Variadic-аргументы** — `...$args` поддерживаются;
- **Значения по умолчанию** — используются, если не переопределены;
- **Интерфейсы и абстрактные классы** — регистрируются через `alias`.

**⚠️ Что стоит учесть**

- **Требуется сборка** — необходимо организовать этап генерации кода. Это нетипично для PHP, но именно это даёт производительность.
