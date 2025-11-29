# Params

С помощью параметров можно проставлять конкретные значения вместо зависимости.

Это бывает необходимо для разрешения buildin параметров, где требуется указать **\$username** или **\$password**, а
также различные **\$options**, также можно передавать конкретные экземпляры объектов, в качестве имени зависимости
используется имя аргумента без \$, например для \$username имя зависимости будет username.

[Пример использования](https://github.com/cekta/di-example-usage/commit/6fb30371083ffb38c8940adf5b948ce07ff3b5c0)

```php
<?php

declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(private string $username, private string $password) 
    {
    }
}

new \Cekta\DI\Compiler(
    containers: [Example::class],
    params: [
        'username' => 'my default username',
        'password' => 'my default password',
    ],
    fqcn: 'App\\Runtime\\Container',
)->compile(); // запишем в файл и можем использовать
```

Важный момент при создании контейнера нужно передавать актуальные значения параметров

```php
<?php

$container = new \App\Runtime\Container([
        'username' => 'my current username',
        'password' => 'my current password',
]);
var_dump($container->get(Example::class));
```

При выполнении мы увидим что \$username = 'my current username' а \$password = 'my current password'.

Это говорит о том что актуальные значения берутся из runtime и могут отличаться от тех что были во время компиляции,
например их можно переопределять с помощью env или любой удобной системой конфигурации.

## Обязательные параметры

Обязательные параметры это те параметры, что были использованные для разрешения зависимостей на этапе компиляции.

При создании экземпляра класса вам необходимо передавать обязательные параметры, так как в случае их недоступности
один или несколько контейнеров могут быть недоступны.

Библиотека сообщит вам исключением в случае если вы не передадите обязательные параметры которые были использованные
при компиляции.

```php
<?php

$container = new \App\Runtime\Container([
//        'username' => 'my current username',
//        'password' => 'my current password',
]); // exception username, password is required
var_dump($container->get(Example::class));
```

## Параметры для конкретных зависимостей

Обычное имя параметра распространяется на все зависимости, но есть специальный способ именования который
распространяется на конкретную зависимость.

```
{Имя зависимости}${имя аргумента}
```

Пример использования

```php
<?php

declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(private string $username, private string $password) 
    {
    }
}

class Example2
{
    public function __construct(
        private string $username, // сюда мы хотим другое значение отправить
        private string $password, // здесь будем использовать дефолтное значение как в Example::class
    ) {
    
}
}

new \Cekta\DI\Compiler(
    containers: [
        Example::class,
        Example2::class,
    ],
    params: [
        'username' => 'my default username',
        'password' => 'my default password',
        Example2::class . '$username' => 'overwritten username for Example2 only',
    ],
    fqcn: 'App\\Runtime\\Container',
)->compile(); // запишем в файл и можем использовать
```

Если в конфигурации указана персональная инструкция, она имеет максимальный приоритет и будет использована.

Такой подход с передачей конкретных параметров помогает например при создании консольных команд, каждой команде
требуется передать **\$name** при этом у каждой команды свое уникальное имя.  
Тут нам на помощь приходит такой синтаксис.

В случае если у аргумента указан тип не buildin то необходимо использовать именно имя аргумента
чтобы его переопределить, так как может быть следующая ситуация:

```php
<?php

declare(strict_types=1);

namespace App;

class Example
{
    public function __construct(
        private A $a1, 
        private A $a2,
        private A $a3,
    ) {
    }
}

class A{}

class B extends A{} // этим классом переопределим

new \Cekta\DI\Compiler(
    containers: [
        Exmple::class,
    ],
    params: [
        Example::class . '$a2' => new B(), // мы переопределили ТОЛЬКО a2, остальные аргументы с помощью autowiring
    ],
    fqcn: '\App\Runtime\Container',
)->compile();
```

С помощью имени аргумнета даже при одинаковых типах мы можем управлять какой именно аргумент необходимо переопределить.

## Lazy (отложенные) параметры

Если значение параметра реализует интерфейс [Lazy](../src/Lazy.php) то такое значение будет каждый раз загружаться в
runtime.

С помощью [LazyClosure](../src/LazyClosure.php) можно вычислять значения с помощью анонимной функции, внутри можно
разметить любой код, который будет вычислять значения, при этом она может обращаться к другим зависимостям.

Это позволит:
1. Формировать одну зависимость на основе других
2. Если для создания зависимости есть factory или builder который умеет создавать экземпляр класса
3. Если не все параметры можно передать в конструктор и необходимо после создания вызывать методы (legacy).

```php
<?php

declare(strict_types=1);

namespace App;

class Example {
    public function __construct(
        private string $dsn,
        private ?string $username = null,
        private ?string $password = null,
    ) {
    }
}

new \Cekta\DI\Compiler(
    containers: [
        Example::class,
        'db_type',
        'db_path',
    ],
    params: [
        'username' => $this->env['DB_USERNAME'] ?? null,
        'password' => $this->env['DB_PASSWORD'] ?? null,
        'dsn' => new \Cekta\DI\LazyClosure(function (\Psr\Container\ContainerInterface $c) {
            return "{$container->get('db_type')}:{$container->get('db_path')}";
        }),
        'db_type' => $this->env['DB_TYPE'] ?? 'sqlite',
        'db_path' => $this->env['DB_PATH'] ?? './db.sqlite',
    ],
)->compile();
```

Обратите внимание раз параметры db_type и db_path вызываются с помощью get внутри Lazy, то рекомендуется добавить их в 
containers, чтобы гарантировать их наличие в runtime.