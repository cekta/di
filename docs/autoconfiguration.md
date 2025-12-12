# Автоматическая конфигурация зависимостей

Иногда вручную перечислять все containers в конфигурации неудобно. Гораздо проще, когда инструмент сам находит точки
входа в приложение.

## 🎯 Когда это нужно?

Автоматическая конфигурация полезна, когда:

* **Много однотипных зависимостей** - контроллеры, команды, middleware
* **Часто добавляются новые точки входа** - не нужно каждый раз обновлять конфигурацию
* **Хочется меньше ручной работы** - автоматизация экономит время

## 🔍 Как это работает?

Точки входа обычно имеют отличительные признаки:

| Тип                | 	Признак                                                                                                                         |
|--------------------|----------------------------------------------------------------------------------------------------------------------------------|
| HTTP-обработчики   | 	Реализуют [RequestHandlerInterface](https://github.com/php-fig/http-server-handler/blob/master/src/RequestHandlerInterface.php) |
| Middleware         | 	Реализуют [MiddlewareInterface](https://github.com/php-fig/http-server-middleware/blob/master/src/MiddlewareInterface.php)      |
| Консольные команды | 	Наследуются от [Command (Symfony)](https://github.com/symfony/console/blob/7.3/Command/Command.php)                             |
| Фоновые задачи     | 	Собственные интерфейсы или атрибуты                                                                                             |

**Важно:** На текущий момент `cekta/di` не занимается сканированием проекта.   
Это инструмент для генерации контейнера на основе готовой конфигурации.   
Вы можете легко создать сканирование самостоятельно или использовать подходящий пакет.

## 🚀 Практический пример

[Демонстрация на GitHub](https://github.com/cekta/di-example-usage/commit/0a8bd1c45e522571fe5347943e658b39fa257355)

### Шаг 1: Подготовка автозагрузчика

Включите оптимизацию автозагрузчика в composer.json:

```json
{
  "config": {
    "optimize-autoloader": true
  }
}
```

Обновите автозагрузчик:

```
composer dumpautoload
```

Теперь в файле `/vendor/composer/autoload_classmap.php` содержится массив всех классов вашего проекта.

### Шаг 2: Создаём сканер классов

/src/ProjectDiscovery.php:

```php
<?php
declare(strict_types=1);

namespace App;

use ReflectionClass;
use Throwable;

class ProjectDiscovery
{
    private array $implements = [];
    private array $containers = [];

    public function __construct(
        private readonly iterable $classes,
    ) {}

    public function containerImplement(string $interface, array $exclude = []): self
    {
        $this->implements[$interface] = $exclude;
        return $this;
    }

    public function loadContainers(): array
    {
        foreach ($this->classes as $className) {
            try {
                $class = new ReflectionClass($className);
            } catch (Throwable) {
                continue;
            }
            $this->checkImplement($class);
        }
        return array_unique($this->containers);
    }

    private function checkImplement(ReflectionClass $class): void
    {
        foreach ($this->implements as $interface => $excludes) {
            if ($class->implementsInterface($interface) && !in_array($class->getName(), $excludes)) {
                $this->containers[] = $class->getName();
            }
        }
    }
}
```

### Шаг 3: Используем сканер в сборке

/bin/build.php:

```php
<?php
declare(strict_types=1);

namespace App;

use Cekta\DI\Configuration;

// Интерфейс-маркер для автоматического добавления
interface MarkerForContainer {}

// Классы, которые хотим добавить автоматически
class A implements MarkerForContainer {}
class B implements MarkerForContainer {}

// Получаем все классы проекта
$classMap = require __DIR__ . '/../vendor/composer/autoload_classmap.php';
$classes = array_keys($classMap);

// Сканируем и находим нужные классы
$discover = new ProjectDiscovery($classes);
$discover->containerImplement(MarkerForContainer::class);
$containers = $discover->loadContainers();

// Генерируем контейнер
$code = (new Configuration(
    containers: $containers,
    fqcn: 'App\\Runtime\\Container',
))->compile();

file_put_contents(__DIR__ . '/../runtime/Container.php', $code);
```

### Шаг 4: Использование

app.php:

```php
<?php
declare(strict_types=1);

namespace App;

$container = new \App\Runtime\Container([]);

// Все классы, реализующие MarkerForContainer, теперь доступны
$a = $container->get(A::class);
$b = $container->get(B::class);
```

## ⚙️ Автоматизация процесса

Добавьте в `composer.json`:

```json
{
  "scripts": {
    "post-autoload-dump": [
      "php ./bin/build.php"
    ]
  }
}
```

Теперь после каждой установки/обновления пакетов контейнер будет обновляться автоматически, проверим:

/src/C.php - Создадим новый класс
```php
<?php
declare(strict_types=1);

namespace App;

class C implements MarkerForContainer {}
```

Обновим список классов проекта и Container.php

```
composer dumpautoload  # Или composer install/update
```

app.php:
```php
<?php
declare(strict_types=1);

namespace App;

$container = new \App\Runtime\Container([]);

// Все классы, реализующие MarkerForContainer, теперь доступны
$a = $container->get(A::class);
$b = $container->get(B::class);
$c = $container->get(C::class); // теперь доступен и наш новый класс
```

## 💡 Лучшие практики

1. **Используйте интерфейсы-маркеры** - создавайте специализированные интерфейсы для разных типов зависимостей
2. **Исключайте ненужные классы** - используйте параметр $exclude для исключения абстрактных классов или тестов
3. **Предпочитайте autowiring** - большая часть кода в вашем проекте не требует настроек!

---

Автоматическая конфигурация значительно упрощает поддержку больших проектов. Начальная настройка требует некоторых
усилий, но затем она экономит время и снижает вероятность ошибок при добавлении новых зависимостей.