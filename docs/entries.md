# Entries

Entries (точки входа) — объявление классов, которые контейнер обязан уметь создавать.  
Это корневые узлы графа зависимостей: от них автоматически строится вся цепочка
внедрения через конструкторы.  

Что перечислять в entries:

- **Обработчики HTTP** — контроллеры, middleware, маршрутизаторы.
- **Консольные команды** — команды, регистрируемые в приложении.
- **Обработчики фоновых задач** — очереди, воркеры.
- **Корневые сервисы** — классы верхнего уровня, которым нужны зависимости.

При вызове `$container->get()` entry — это первый узел, с которого начинается разрешение.

При `$container->has()` контейнер вернёт `true` только для классов, объявленных как `entries`.

## Граф зависимостей

Контейнер автоматически обходит зависимости конструкторов:

1. Контейнер получает вызов `$container->get(UserService::class)`.
2. Извлекаются параметры конструктора `UserService` через рефлексию.
3. Для каждого параметра рекурсивно строится дерево зависимостей.
4. Когда зависимости найдены (как entries, params, singletons, factories) — они
   становятся узлами графа.
5. Генерируется чистый PHP-код с `new`-вызовами.

Вложенность зависит от конструкторов и может быть любой глубины.

## Пример

**src/Example.php**

```php
<?php
declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(public A $a)
    {}
}
```

**src/A.php**

```php
<?php
declare(strict_types=1);

namespace App;

class A
{
    public function __construct(public B $b)
    {}
}
```

**src/B.php**

```php
<?php
declare(strict_types=1);

namespace App;

class B
{
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
                'entries' => [
                    \App\Example::class,
                ]
            ],
        );
    }
}
```

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$factory = new \Cekta\DI\ContainerFactory();
$container = $factory->create(new \App\Manifest());

assert(true === $container->has(\App\Example::class));
assert(false === $container->has(\App\A::class));
assert(false === $container->has(\App\B::class));

$example = $container->get(\App\Example::class);
assert($example->a instanceof \App\A);
assert($example->a->b instanceof \App\B);
```
