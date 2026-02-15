# Alias (Псевдонимы)

Механизм псевдонимов позволяет заменять одну зависимость другой. Это полезно для:

1. **Выбора реализации интерфейса** - указание, какой конкретный класс использовать для интерфейса.
2. **Выбора наследника абстрактного класса** - определение конкретной реализации абстрактного класса.
3. **Замены зависимостей** - использование подклассов вместо родительских классов.
4. **Сокращения имен** - создание коротких псевдонимов для длинных имен классов

## Пример

**src/Example.php**

```php
<?php

namespace App;

class Example {
    public function __construct(
        public I $i,
        public Base $base,
    ) {}
}
```

**src/I.php**

```php
<?php

namespace App;

interface I {}
```

**src/R2.php**

```php
<?php

namespace App;

class R2 implements I {}
```

**src/Base.php**

```php
<?php

namespace App;

class Base {}
```

**src/E1.php**

```php
<?php

namespace App;

class E1 extends Base {}
```

**bin/build.php** - build

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$fqcn = 'App\Container';
$filename = __DIR__ . '/../src/Container.php';

file_put_contents(
    $filename,
    (new \Cekta\DI\ContainerBuilder(
        fqcn: $fqcn,
        entries: [\App\Example::class],
        alias: [
            I::class => R2::class,      // Для I используем R2
            Base::class => E1::class,   // Для Base используем E1
    ],
    ))->build()
);
```

**Генерируем Container**

```
php bin/build.php
```

**index.php** - использование (use)

```php
<?php
declare(strict_types=1);

namespace App;

$container = new \App\Container([]);
$example = $container->get(Example::class);

$example->i instanceof R2;      // true
$example->base instanceof E1;   // true
```

**Важно**: Псевдонимы устанавливаются на этапе компиляции. Для их изменения требуется повторная генерация контейнера.