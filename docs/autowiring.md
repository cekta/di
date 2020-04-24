# Автоматическая конфируция

На этапе разработки, можно автоматически конфигурировать зависимости на основе конструктора с помощью 
[Autowiring](../src/Provider/Autowiring.php), который используе 
[PHP Reflection](https://www.php.net/manual/ru/book.reflection.php) анализирует аргументы конструктора.

Рассмотрим как определяется имена зависимостей для аргументов.

/src/Example.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class Example
{
    public function __construct(A $a, int $b)
    {
        var_dump($a, $b);
    }
}
```

/src/A.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class A
{

}
```

/src/MyContainer.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\KeyValue;

class MyContainer extends Container
{
    public function __construct()
    {
        $providers[] = new KeyValue(['b' => 123]);
        $providers[] = new Autowiring(new Reflection());
        parent::__construct(...$providers);
    }
}
```

/public/index.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\MyContainer;

require __DIR__ . '/../vendor/autoload.php';

$container = new MyContainer();
var_dump($container->get(\Cekta\DI\Example::class));
```

Выполнение кода:
``` 
$ php public/index.php 
/src/Example.php:11:
class Cekta\DI\A#7 (0) {
}
/src/Example.php:11:
int(123)
/public/index.php:10:
class Cekta\DI\Example#9 (0) {
}
```

Порядок определения имени зависимости для аргумента:
1. Если указан тип аргумента отличный от примитивного, используется он (например Cekta\DI\A).
2. В иных случаяз в качестве зависимости указывается имя аргумента, без символа $.
