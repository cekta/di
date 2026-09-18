## Как формируются имена зависимостей {#dependency-naming}

Лучше рассмотреть конкретный пример.

```php
<?php
namespace App;

class Example
{
    public function __construct(
        int $arg_1,
        Foo $arg_2,
        string|int $arg_3,
        int|string $arg_4,
        Foo|string $arg_5,
        string|Foo $arg_6,
        ?int $arg_7,
        ?Foo $arg_8,
        $arg_9,
        I $arg_10
        Foo|int ...$arg_11,
    ) {
    }
}

class Foo {}

interface I {}
```

| Имя аргумента | Глобавльное имя   | Локальное имя                      |
|---------------|-------------------|------------------------------------|
| arg_1         | "arg_1"           | Example::class . '$arg_1'          |
| arg_2         | "App\Foo"         | Example::class . '$arg_2'          |
| arg_3         | "string\|int"     | Example::class . '$arg_3'          |
| arg_4         | "string\|int"     | Example::class . '$arg_4'          |
| arg_5         | "App\Foo\|string" | Example::class . '$arg_5'          |
| arg_6         | "App\Foo\|string" | Example::class . '$arg_6'          |
| arg_7         | "arg_7"           | Example::class . '$arg_7'          |
| arg_8         | "?App\Foo"        | Example::class . '$arg_8'          |
| arg_9         | "arg_9"           | Example::class . '$arg_9'          |
| arg_10        | "App\I"           | Example::class . '$arg_10'         |
| arg_11        | "...App\Foo\|int" | '...' . Example::class . '$arg_11' |

**Имя аргумента != имени зависимости**

В конфигурации DI надо использовать именно имена зависимостей.

## Глобальное vs Локальное имена зависимостей. {#global_vs_local}

**Глобальное имя** зависимости используется на уровне всего проекта, использовать такие имена удобно чтобы один раз
зарегистрировать значение и использовать его в разных зависимостях, например реализацию интерфейса или абстрактного класса.

**Локальное имя** это возможность переопределить зависимость в конкретном ОДНОМ месте, точечно.  
С помощью таких имен удобно задавать примитивные параметры вроде логинов или паролей и тд.

## Приоритет загрузки зависимостей. {#priority}

Библиотека предоставляет несколько способов определения зависимостей. Важно понимать, в каком порядке они
применяются - это помогает избежать неожиданного поведения и правильно настроить контейнер.

```php
<?php

namespace App;

class Example
{
    public function __construct(
        public \stdClass $std_class,
    ) {
    
}
}

$class1 = new \stdClass();
$class1->id = 1;

$class2 = new \stdClass();
$class2->id = 2;

$class3 = new \stdClass();
$class3->id = 3;

$class4 = new \stdClass();
$class4->id = 4;

(new Cekta\DI\ContainerBuilder(
    entries: [Example::class],
    params: [
        Example::class . '$stdClass' => $class1, 
        \stdClass::class => $class3,
        'class2' => $class2,
        'class4' => $class4,
    ],
    alias: [
        Example::class . '$stdClass' => 'class2',
        \stdClass::class => 'class4',
    ],
))->build();
```

При запросе `Example::class` контейнер ищет зависимость в следующем порядке (выше в таблице приоритетней):

| 	Способ                   | 	В нашем примере                                                 |
|----------------------------|--------------------------------------------------------------------|
| Локальное имя из `params`  | `Example::class . '$' . stdClass` резульатат `$class1`     |
| Локальное имя из `alias`   | `Example::class . '$' . stdClass` резульатат `$class2`     |
| Глобальное имя из `params` | `\stdClass::class` результат `$class3`                             |
| Глобальное имя из `alias`  | `\stdClass::class` результат `$class4`                             |
| Значение по умолчанию      | значение по умолчанию не заданно в конструкторе и будет пропущенно |
| Autowiring в конструктор   | Создает `\stdClass` через Autowiring                               |
