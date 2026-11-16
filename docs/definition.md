# Definition

Параметры которые необходимы только на этапе генерации контейнера, этим параметры в будующем лежат в контейнере.

## entries

Точки входа в приложения, которые `Container` должен уметь разрешать.

Если контейнер скомпилировался значит он сможет их получать.

[Подробней](entries.md)

## alias

Механимз замены зависимостей, с одной на другую.

Помогает регистрировать реализацию интерфейса или абстракного класса и внедрять ее.

[Подробней](alias.md

## singletons

Именя зависимостей которые должны создаваться однакратно и использоваться во **всех** случаях.

[Подробней](lifecycle.md)

## factories

Именя зависимостей которые будут создаваться каждый раз как их запросят.

[Подробней](lifecycle.md)

## Отложенная инициализация definition

В виду того что `definition` могут содержать большое количество параметров, 
при этом они нужны только единожды на этапе компиляции, а в дальнейшем не 
используются - нет никакого смысла формировать их каждый раз.

Для получения `definition` используется метод `definition()` который вызывает только на этапе компиляции, 
вы можете его расширить и подготавливать параметры там.

**src/Manifest.php**

```php
<?php

declare(strict_types=1);

namespace App;

class Manifest extends \Cekta\DI\Manifest
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

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        // любой удобный способ генерации и получения зависимостей.
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

Такой способ позволяет объявлять definition только тогда когда они действительно нужны.

Это может быть полезно если вы формируете `entries` на основе содержимого проекта - [подробней в discovery](discovery.md).
