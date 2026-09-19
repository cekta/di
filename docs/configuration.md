# Проект

Проект конфигурируется в классе, наследуемом от [`AbstractProject`](api:AbstractProject).

В конструкторе передаются данные (путь генерации, параметры), а в `definition()` — зависимости и их поведение.

**src/Project.php**

```php
<?php

declare(strict_types=1);

namespace App;

class Project extends \Cekta\DI\AbstractProject
{
    public function __construct(public readonly array $env = getenv())
    {
        parent::__construct(
            filename: __DIR__ . '/../Container.php',
            fqcn: 'App\Container',
            params: [
                \PDO::class . '$dsn' => $env['DB_DSN'] ?? 'sqlite:' . __DIR__ . '/../mydb.sqlite',
            ],
        );
    }

    public function definition(): array
    {
        return [
            'entries' => [
                \App\Example::class,
            ],
            'alias' => [
                \Psr\Log\LoggerInterface::class => \Monolog\Logger::class,
            ],
            'singletons' => [
                \App\Service\Cache::class,
            ],
            'factories' => [
                \App\Service\RequestHandler::class,
            ],
        ];
    }
}
```

## Конструктор

| Параметр   | Назначение                                         | Подробнее                     |
|-----------|----------------------------------------------------|-------------------------------|
| `filename` | Путь к файлу сгенерированного контейнера           | —                             |
| `fqcn`     | Полное квалифицированное имя класса контейнера     | —                             |
| `params`   | Скалярные значения для аргументов конструкторов    | [Параметры](params.md)        |

## Зависимости

В методе `definition()` задаются зависимости и их поведение:

| Ключ         | Назначение                                         | Подробнее                |
|--------------|----------------------------------------------------|--------------------------|
| `alias`      | Привязка интерфейсов и абстракций к реализациям    | [Алиасы](alias.md)       |
| `singletons` | Один экземпляр на весь PHP-процесс                 | [Жизненный цикл](lifecycle.md) |
| `factories`  | Новый экземпляр при каждом `get()`                 | [Жизненный цикл](lifecycle.md) |

> 💡 Классы, указанные в `entries`, по умолчанию ведут себя как **scoped** — один экземпляр на контейнер.
> Полное сравнение жизненных циклов — [в разделе «Управление жизненным циклом»](lifecycle.md).
