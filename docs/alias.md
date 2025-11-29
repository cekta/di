# Alias

Механизм alias позволяет заменить одну зависимость другой, это бывает необходимо:
1. Какую именно реализацию интерфейса необходимо использовать.
2. Какой именно наследник абстрактного класса использовать.
3. Для замены одной зависимости с помощью другой, например наследником класса.
4. Для того чтобы регистрировать короткие имена зависимостей и ссылаться на длинные.

```php
<?php

declare(strict_types=1);

namespace App;

class Example {
    public function __construct(
        public I $i,
        public Base $base,
    ) 
    {
    }
}

interface I {
    
}

class R1 implements I{}
class R2 implements I{}

class Base {
    
}

class E1 extends Base{}
class E2 extends Base{}

new \Cekta\DI\Compiler(
    containers: [Example::class],
    alias: [
        I::class => R2::class,
        Base::class => E1::class,
        'short-name' => Example::class,
    ],
    fqcn: 'App\\Runtime\\Container',
)
```

usage
```php
<?php

declare(strict_types=1);

namespace App;

$container = new \App\Runtime\Container([]);
$example = $container->get(Example::class);
assert($example->i instanceof R2);
assert($example->base instanceof E1);
assert($example === $container->get('short-name'));
```

Параметры проставляются на этапе компиляции чтобы их переопределить необходимо повторно компилировать class.

## Параметры для конкретных зависимостей

Можно задавать alias которые будут применяться ТОЛЬКО к конкретной зависимости.

Задание alias для конкретного аргумента
```
{Имя зависимости}${имя аргумента}
```

```php
<?php

declare(strict_types=1);

namespace App;

class Example {
    public function __construct(
        public I $i,
        public I $i2,
        public I $i3,
    ) 
    {
    }
}

interface I {
    
}

class R1 implements I{}
class R2 implements I{}

new \Cekta\DI\Compiler(
    containers: [Example::class],
    alias: [
        I::class => R2::class,
        Example::class . '$i2' => R1::class,
    ],
    fqcn: 'App\\Runtime\\Container',
)
```

usage
```php
<?php

declare(strict_types=1);

namespace App;

$container = new \App\Runtime\Container([]);
$example = $container->get(Example::class);
assert($example->i instanceof R2);
assert($example->i2 instanceof R1);
assert($example->i3 instanceof R2);
```

Как можно заметить в $i2 мы передали R1::class что отличается от значения по умолчанию.

Поведение абсолютно аналогичное, что и для params.