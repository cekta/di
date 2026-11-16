# Начало работы

Этот раздел — пошаговое руководство, которое покажет, как настроить и использовать `Cekta\DI`.

## Без DI-контейнера

Давайте представим простейший проект с классом `Example`, зависящим от подключения к базе данных (PDO).

**src/Example.php**
```php
<?php
declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(
        private \PDO $db,
    ) {
    }

    public function payload(): void
    {
        // ... используйте $db ...
    }
}
```

**index.php** — ручное создание зависимости:

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$pdo = new \PDO(getenv('DB_DSN') ?? 'sqlite:' . __DIR__ . '/../mydb.sqlite');
$example = new \App\Example($pdo);
$example->payload();
```

Проблема: каждый, кто использует `Example`, должен вручную создавать и передавать `\PDO`.  
При росте проекта это быстро становится хаосом.

## С DI-контейнером

Теперь то же самое, но зависимости управляются централизованно через `Cekta\DI`.

### 1. Установка

```bash
composer require cekta/di
```

### 2. Конфигурация проекта

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
                'entries' => [\App\Example::class],
            ],
        );
    }
}
```

[Подробней о manifest](manifest.md)

### 3. Генерация контейнера

**bin/generate.php** — скрипт для генерации сконфигурированного контейнера:

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$manifest = new \App\Manifest();
$generator = new \Cekta\DI\ContainerGenerator();
file_put_contents($manifest->filename(), $generator->generate($manifest));
echo "{$manifest->filename()} was generated" . PHP_EOL;
```

Запустите скрипт один раз, чтобы создать **Container.php**:

```bash
php bin/generate.php
```

Скрипт создаётся один раз и переиспользуется при необходимости перегенерации.  
Он читает конфигурацию из `Manifest` и генерирует готовый файл контейнера.

[Подробней о генерации](generate.md)

### 4. Использование контейнера

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$factory = new \Cekta\DI\ContainerFactory();
$container = $factory->create(new \App\Manifest());

$example = $container->get(\App\Example::class);
$example->payload();
```

`ContainerFactory` создаёт экземпляр контейнера и подставляет параметры из [manifest](manifest.md).

Зависимости разрешаются автоматически — `\PDO` будет передан в конструктор `Example` без ручной настройки.

### Преимущества

Однажды сконфигурированная зависимость доступна всем, кто в ней нуждается — без лишних настроек и погружений в детали ее работы.

Допустим, у вас есть `Example`, `ReportGenerator` и `ConsoleCommand`, всем нужен `\PDO`. Вам не нужно настраивать подключение к базе каждый раз: достаточно описать параметр один раз в конфигурации, а контейнер сам внедрит его в любые классы, которым он требуется.

Это особенно удобно в команде: разработчик пишет код, опираясь на типизированные зависимости, и не заботится о деталях их создания.
