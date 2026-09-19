# Отложенные значения (Lazy)

Большинство параметров вычисляются на этапе компиляции контейнера. Однако есть сценарии, когда
значение нужно получить только в момент использования. Для таких случаев в библиотеке
предусмотрен интерфейс [`Lazy`](#интерфейс-lazy), позволяющий отложить вычисление до рантайма.

Lazy-параметры полезны, когда:

1. Нужно создать объект через callback, например, внедряя зависимости в методы после создания.
2. Нужно получить ссылку на текущий контейнер для реализации паттерна Service Locator.
3. Нужно сгенерировать значение на основе других параметров, например, DSN-строку подключения
   к БД из `db_type`, `db_host`, `db_name` и т. д.

Подробнее о параметрах — в разделе [Параметры](params.md).

---

## Интерфейс Lazy

```php
<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

/**
 * @external
 */
interface Lazy
{
    public function load(ContainerInterface $container): mixed;
}
```

Интерфейс [`Lazy`](https://github.com/cekta/di/blob/main/src/Lazy.php) помечен как **external** —
он предназначен для использования пользователями библиотеки. Вы можете создать собственную
реализацию, если стандартные решения не покрывают ваши потребности.

Все реализации `Lazy` вызывают метод `load()` в момент разрешения зависимости — уже внутри
скомпилированного контейнера. Это позволяет получить доступ к экземпляру `ContainerInterface`
и выполнить произвольную логику.

В библиотеке есть две готовые реализации.

### Closure

[Исходный код](https://github.com/cekta/di/blob/main/src/Lazy/Closure.php)

Принимает callback-функцию и вызывает её в момент разрешения зависимости. Позволяет реализовать
любую логику создания и возврата значения.

### Container

[Исходный код](https://github.com/cekta/di/blob/main/src/Lazy/Container.php)

Возвращает сам контейнер при вызове `load()`. Используется, когда классу нужен доступ к контейнеру
для разрешения зависимостей, например, при реализации паттерна Service Locator.

---

## Примеры

### Пример 1: Внедрение зависимостей через метод

Обычно зависимости передаются через конструктор. Однако бывают ситуации, когда требуется
внедрить зависимость в метод или выполнить дополнительную инициализацию после создания объекта.

**src/Example.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Example
{
    private \PDO $connection;

    public function setPdo(\PDO $connection)
    {
        $this->connection = $connection;
    }
}
```

**src/Project.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Project extends \Cekta\DI\AbstractProject
{
    public function __construct(private array $env)
    {
        parent::__construct(
            filename: __DIR__ . '/../Container.php',
            fqcn: 'App\Container',
            params: [
                \App\Example::class . '$connection' => new \Cekta\DI\Lazy\Closure(
                    function (\Psr\Container\ContainerInterface $c) {
                        $example = new \App\Example();
                        $example->setPdo($c->get(\PDO::class));
                        return $example;
                    }
                ),
            ],
        );
    }

    public function definition(): array
    {
        return [
            'entries' => [\App\Example::class, \PDO::class],
            'alias' => [],
            'singletons' => [],
            'factories' => [],
        ];
    }
}
```

### Пример 2: Внедрение ContainerInterface

Иногда классу требуется доступ к контейнеру для разрешения зависимостей. Например,
при реализации паттерна Service Locator.

**src/Example.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(
        private \Psr\Container\ContainerInterface $container
    ) {
    }
}
```

**src/Project.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Project extends \Cekta\DI\AbstractProject
{
    public function __construct()
    {
        parent::__construct(
            filename: __DIR__ . '/../Container.php',
            fqcn: 'App\Container',
            params: [
                \Psr\Container\ContainerInterface::class => new \Cekta\DI\Lazy\Container(),
            ],
        );
    }

    public function definition(): array
    {
        return [
            'entries' => [\App\Example::class],
            'alias' => [],
            'singletons' => [],
            'factories' => [],
        ];
    }
}
```

---

## Рекомендации

1. Если параметры используются внутри `Closure`, добавляйте их в `entries` для гарантии доступности
2. Предпочтительный способ внедрения зависимостей — конструктор. Используйте `Lazy` только когда это невозможно
