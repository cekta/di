# Алиасы

Алиасы — маппинг одной зависимости (А) на другую (B).

Когда использовать:

- **Привязка интерфейса к реализации** — контейнер видит тип `Logger`(А) и создаёт `ConsoleLogger`(B).
- **Выбор наследника** — вместо базового класса(A) подставляется конкретный наследник(B).
- **Замена зависимостей** — можно подменить реализацию, не трогая код потребителя.

## Пример простой

**src/Example.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(
        public Logger $logger,
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
            definition: [
                'entries' => [Example::class],
                'alias' => [
                    // Глобальный алиас — для всех, кто просит Logger
                    Logger::class => ConsoleLogger::class,
                ],
                'singletons' => [],
                'factories' => [],
            ]
        );
    }
}
```

> ⚠️ Алиасы определяются на этапе **генерации контейнера**.

> Для изменения маппинга потребуется пересобрать контейнер.
