# Алиасы

Алиасы — маппинг абстрактных типов (интерфейсов, абстрактных классов) на конкретные реализации.  

## Когда использовать

- **Привязка интерфейса к реализации** — контейнер видит тип `Logger` и создаёт `ConsoleLogger`.
- **Выбор наследника** — вместо базового класса подставляется конкретный наследник.
- **Замена зависимостей** — можно подменить реализацию, не трогая код потребителя.

## Пример

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
        );
    }

    public function definition(): array
    {
        return [
            'entries' => [Example::class],
            'alias' => [
                // Глобальный алиас — для всех, кто просит Logger
                Logger::class => ConsoleLogger::class,
            ],
            'singletons' => [],
            'factories' => [],
        ];
    }
}
```

Использование скомпилированного контейнера описано в [Начале работы](start.md).

> ⚠️ Алиасы определяются на этапе **компиляции**. Для изменения маппинга потребуется пересобрать контейнер.
