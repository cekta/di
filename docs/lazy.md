# Отложенные значения (Lazy)

Есть сценарии, когда значение нужно получить только в момент использования.  
Для таких случаев в библиотеке предусмотрен интерфейс [`Lazy`](https://github.com/cekta/di/blob/master/src/Lazy.php).

Если значение параметра реализует `Lazy` значит значение будет каждый раз вычислятся в runtime.

Lazy-параметры полезны, когда:

1. Нужно создать объект через callback, например, внедряя зависимости в методы после создания.
2. Нужно получить ссылку на текущий контейнер для реализации паттерна Service Locator.
3. Нужно сгенерировать значение на основе других параметров, например, DSN-строку подключения
   к БД из `db_type`, `db_host`, `db_name` и т. д.

В библиотеке есть две готовые реализации: [`Closure`](https://github.com/cekta/di/blob/main/src/Lazy/Closure.php) и 
[`Container`](https://github.com/cekta/di/blob/main/src/Lazy/Container.php).

Интерфейс [`Lazy`](https://github.com/cekta/di/blob/main/src/Lazy.php) помечен как **external** —
он предназначен для использования пользователями библиотеки.  
Вы можете создать собственную реализацию, если стандартные решения не покрывают ваши потребности.

Все реализации `Lazy` вызывают метод `load()` в момент разрешения зависимости — уже внутри
скомпилированного контейнера. Это позволяет получить доступ к текущему экземпляру `ContainerInterface`
и выполнить произвольную логику.

## `Lazy\Closure`

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

**src/Manifest.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Manifest extends \Cekta\DI\Manifest
{
    public function __construct(private array $env)
    {
        parent::__construct(
            filename: __DIR__ . '/../Container.php',
            fqcn: 'App\Container',
            params: [
                \App\Example::class => new \Cekta\DI\Lazy\Closure(
                    function (\Psr\Container\ContainerInterface $c) {
                        $example = new \App\Example();
                        $example->setPdo($c->get(\PDO::class));
                        return $example;
                    }
                ),
            ],
            definition: [
                'entries' => [\App\Example::class, \PDO::class],
            ],
        );
    }
}
```

Рекомендации:

1. Если параметры используются внутри `Lazy\Closure`, добавляйте их в `entries` для гарантии доступности
2. Предпочтительный способ внедрения зависимостей — конструктор. Используйте `Lazy` только когда это невозможно

## `Lazy\Container`

Иногда классу требуется доступ к контейнеру для разрешения зависимостей. Например,
при реализации паттерна **Service Locato**r.

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

**src/Manifest.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Manifest extends \Cekta\DI\Manifest
{
    public function __construct()
    {
        parent::__construct(
            filename: __DIR__ . '/../Container.php',
            fqcn: 'App\Container',
            params: [
                \Psr\Container\ContainerInterface::class => new \Cekta\DI\Lazy\Container(),
            ],
            definition: [
                'entries' => [\App\Example::class],
            ],
        );
    }
}
```



## Пример изменение реализации без перегенерации контейнера

Иногда хочется иметь возможность менять реализацию без перегенерации контейнера.

Например менять реализацию Logger в runtime.

**src/Example.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Manifest
{
    public function __construct(public Logger $logger )
    {}
}
```

**src/Manifest.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Manifest extends \Cekta\DI\Manifest
{
    private array $availableLoggers = [
        'console' => ConsoleLogger::class,
        'file' => FileLogger::class,
    ];
    
    public function __construct(private array $env = getenv())
    {
        parent::__construct(
            filename: __DIR__ . '/../Container.php',
            fqcn: 'App\Container',
            params: [
                Logger::class => new Cekta\DI\Lazy\Closure(function(ContainerInterface $c){
                    return $this->get(
                        $this->availableLoggers[$this->env['APP_LOGGER'] ?? 'console'] ?? ConsoleLogger::class
                    );
                }),
            ],
            definition: [
                'entries' => [
                    Example::class, 
                    ConsoleLogger::class, 
                    FileLogger::class
                ],
            ]
        );
    }
}
```

По умолчанию будет использоваться `ConsoleLogger::class`, но реализацию можно поменять на `FileLogger::class` без 
перегенерации контейнера.

```sh
export APP_LOGGER=file
php index.php
```

> ⚠️ Старайтесь ограничить доступные значения (`console`, `file` и т. д.) чтобы реализации можно было указать в `entries`.
