# Начало работы

## Установка
```
composer require cekta/di
```

## Настройка проекта

### 1. Создайте структуру проекта (опционально)

Создайте папку для сгенерированного кода:

```
mkdir runtime
```

Обновите `composer.json`, добавив автозагрузку:

```
"autoload": {
  "psr-4": {
    "App\\": "src/",
    "App\\Runtime\\": "runtime/"
  }
}
```

Выполните:

```
composer dumpautoload
```

### 2. Создайте тестовый класс

src/Example.php:

```php
<?php
declare(strict_types=1);

namespace App;

class Example
{
}
```

### 3. Настройте класс проекта

src/Project.php:

```php
<?php
declare(strict_types=1);

namespace App;

use Cekta\DI\Configuration;
use Psr\Container\ContainerInterface;
use RuntimeException;

class Project
{
    private string $container_file;
    private string $container_fqcn = 'App\\Runtime\\Container';

    public function __construct(private array $env)
    {
        $this->container_file = realpath(__DIR__ . '/..') . '/runtime/Container.php';
    }

    public function createContainer(): ContainerInterface
    {
        if (!class_exists($this->container_fqcn)) {
            throw new RuntimeException("$this->container_fqcn не найден");
        }
        return new ($this->container_fqcn)($this->params());
    }

    public function compile(): void
    {
        $content = (new Configuration(
            containers: [Example::class],
            params: $this->params(),
            fqcn: $this->container_fqcn,
        ))->compile();

        if (file_put_contents($this->container_file, $content, LOCK_EX) === false) {
            throw new RuntimeException("Не удалось сгенерировать $this->container_file");
        }
        chmod($this->container_file, 0777);
    }

    private function params(): array
    {
        return [];
    }
}
```

### 4. Создайте скрипт генерации

/bin/build.php:

```php
#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$project = new \App\Project($_ENV);
$project->compile();
```

### 5. Сгенерируйте контейнер

```
php bin/build.php
```

### 6. Проверьте работу

/app.php

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

$project = new \App\Project($_ENV);
$container = $project->createContainer();

var_dump($container->get(App\Example::class));
```

Запустите:

```
php app.php
```

Ожидаемый вывод:

```
object(App\Example)#1 (0) {
}
```

### 7. Настройте автоматическую генерацию (опционально)

Добавьте в composer.json:

```json
{
  "scripts": {
    "post-autoload-dump": ["php ./bin/build.php"]
  },
  "config": {
    "optimize-autoloader": true
  }
}
```

Теперь при обновлении автозагрузки контейнер будет генерироваться автоматически:

```
composer dumpautoload
```

### Примеры реализации

* [Создание проекта](https://github.com/cekta/di-example-usage/commit/dcd1edaad83c6ebe621a5c9ae48cb11c634a7bdc)
* [Интеграция cekta/di](https://github.com/cekta/di-example-usage/commit/c071b21fac50bdee943dd477b5f2c140c9608668)

### Дальнейшие шаги

После настройки используйте библиотеку следующим образом:

1. Изменяйте конфигурацию в классе `App\Project`
2. Генерируйте контейнер: `php bin/build.php` или `composer dumpautoload`
3. Используйте контейнер в приложении: `$container->get(Service::class)`

Готово к использованию!