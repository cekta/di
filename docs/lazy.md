# 🕐 Lazy-параметры (отложенные значения)

Некоторые параметры могут разрешаться в момент использования, а не во время build Container, такие параметры называются
Lazy (ленивые). С помощью таких параметров можно реализовать свою callback функцию, которая будет
генерировать зависимость в особо сложных случаях:

1. Нужно внедрять зависимости не только в конструктор, но и например в методы после создания объекта.
2. Нужно получить текущий Container, для различных service locator которым требуется текущая реализация
   ContainerInterface.
3. Можно генерировать различные значения на основе других, например dsn строку подключения к бд, на основе db_type,
   db_host, db_name и тд.

Если параметр реализует [Lazy interface](../src/Lazy.php), то он будет загружаться в runtime.

## Пример внедрения зависимостей через метод. {#example-method-injection}

Может быть ситуация когда требуется внедрить зависимость не через конструктор, а через метод или другими более сложными 
способами.

**src/Example.php**
```php
<?php

namespace App;

class Example
{
    private \PDO $connection;

    public function setPdo(\PDO $connection)
    {
        // inject via method
        $this->connection = $connection;
    }
}
```

**src/Config.php**
```php
<?php

namespace App;

class Config
{
    public function __construct(private array $env)
    {
    }

    public function __invoke(): array
    {
        return [
            \App\Example::class => new \Cekta\DI\Lazy\Closure(
                function (\Psr\Container\ContainerInterface $c) {
                    $example = new \App\Example();
                    $example->setPdo($c->get(\PDO::class));
                    return $example;
                }
            ),
            \PDO::class . '$dsn' => $this->env['DB_DSN'] ?? 'sqlite:./db.sqlite',
        ];
    }
}
```

**bin/build.php**
```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new \Cekta\DI\ContainerBuilder(
    entries: [\App\Example::class, \PDO::class],
    fqcn: '\App\Container',
    params: (new \App\Config(getenv() + $_ENV))(),
))->build();
```

**index.php**
```php
<?php

reqire_once __DIR__ . '/vendor/autoload.php';

$container = new \App\Container((new Config(getenv() + $_ENV))());
var_dump($container->get(\App\Example::class));
```

[\Cekta\DI\Lazy\Closure](https://github.com/cekta/di/blob/master/src/Lazy/Closure.php) использую callback функцию 
создает любое значение. Вы можете задать любую callback функцию и внедрять любым способом.

Вы можете [управлять жизненном циклом](lifecycle.md) данных зависимостей.

**Рекомендация:** 
1. Если параметры используются внутри LazyClosure, добавьте их в entries для гарантии доступности.
2. Передавайте зависимости через конструктор.

## Пример передачи ContainerInterface в Service Locator. {#example-service-locator}

Service Locator для своей работы может требовать [ContainerInterface](https://www.php-fig.org/psr/psr-11/), 
чтобы разрешать зависимости.

**src/Example.php**
```php
<?php

namespace App;

class Example
{
    public function __construct(private \Psr\Container\ContainerInterface $container) {
    }
}
```

**src/Config.php**

```php
<?php

namespace App;

class Config
{
    public function __construct(private array $env)
    {
    }

    public function __invoke(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => new \Cekta\DI\Lazy\Container(),
        ];
    }
}
```

**bin/build.php**
```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new \Cekta\DI\ContainerBuilder(
    entries: [\App\Example::class],
    fqcn: '\App\Container',
    params: (new \App\Config(getenv() + $_ENV))(),
))->build();
```

**index.php**
```php
<?php

reqire_once __DIR__ . '/vendor/autoload.php';

$container = new \App\Container((new Config(getenv() + $_ENV))());
var_dump($container->get(\App\Example::class));
```

Внедрить текущую реализацию Container можно с помощью Lazy параметра 
[\Cekta\DI\Lazy\Container](https://github.com/cekta/di/blob/master/src/Lazy/Container.php).
