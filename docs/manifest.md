# Manifest

[`Manifest`](https://github.com/cekta/di/blob/master/src/Manifest.php) определяет конфигурацию проекта в 
соответствие с которой внедряются зависимости.

> 💡Cделать наследника от базового класса и в нем хранить все настройки вашего проекта.

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
            definition: [
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
        );
    }
}
```

Позволяет хранить всю конфигурацию проекта в одном месте.

## filename

Имя файла, где будет сгенерирвоан контейнер.

Обязательный параметр.

[Подробней о генерации контейнера](generate.md)

## fqcn

Полное имя класса вместе с namespace, на основе этого параметра сформируется namespace и имя класса у сгенерированного 
контейнера.

Для `App\Container` - namespace = `App`, имя класса `Container`

Обязательный параметр.

## params

Возможность задавать значения аргументов конструктора.

[Подробней](params.md)

## definition

Параметры необходимые и используемые только на этапе [генерации контейнера](generate.md), 
в будующем они "вшиты" в Container.

[Подробней](definition.md)
