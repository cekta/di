# Параметры

Параметры — механизм задания конкретных значений для аргументов конструктора. Они позволяют:

- передавать встроенные типы (`string`, `int`, `array` и т.д.)
- переопределять значения по умолчанию
- передавать конкретные экземпляры объектов

Значения параметров задаются в конструкторе класса, наследуемого от [`AbstractProject`](api:AbstractProject).

## Имена параметров

Каждый параметр имеет **имя** — ключ в конфигурации.  
Имена могут быть [глобальными и локальными](dependency-naming.md).

> 💡 **Рекомендация.** По умолчанию используйте **глобальные** имена. Они делают конфигурацию
> предсказуемой — одна настройка работает для всех потребителей. Локальные имена — для точечного
> переопределения.

## Пример

**src/Example.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}
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
                \App\Example::class . '$firstName' => 'admin',
                \App\Example::class . '$lastName' => 'secret',
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

Полный пример с компиляцией и использованием контейнера описан в [Начале работы](start.md).

## Отложенные значения

Параметры вычисляются каждый раз в runtime (значения runtime переопределяют значения параметров с compile).

Если нужно отложить вычисление до рантайма — см. [Отложенные значения](lazy.md).
