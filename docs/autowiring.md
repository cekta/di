# Автоматическая конфируция

На этапе разработки, можно автоматически конфигурировать зависимости на основе конструктора класса, с помощью 
[Autowiring](../src/Provider/Autowiring.php), который использует 
[PHP Reflection](https://www.php.net/manual/ru/book.reflection.php) анализирует аргументы конструктора и подставляет 
нужные зависимости.

/src/Example.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class Example
{
    /**
     * Example constructor.
     * @param A $a
     * @param int $b
     * @param string $c
     * @inject magic $c
     */
    public function __construct(A $a, int $b, string $c)
    {
        var_dump($a, $b, $c);
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
        $providers[] = new KeyValue(
            [
                'b' => 123,
                'magic' => 'its magic annotation',
            ]
        );
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
/src/Example.php:18:
class Cekta\DI\A#11 (0) {
}
/src/Example.php:18:
int(123)
/src/Example.php:18:
string(20) "its magic annotation"
/public/index.php:10:
class Cekta\DI\Example#9 (0) {
}
```

Порядок определения имени зависимости:
1. Если для аргумента есть анотация @inject то используется она.  
    Для аргумента **$c** анотация указывает на **magic**.
1. Если указан тип аргумента отличный от примитивного, используется он.  
    У аргумента **$a** указан тип **Cekta\DI\A**.
2. В иных случаяз в качестве зависимости указывается имя аргумента, без символа $.  
    У аргумента **$b** ничего не указано, поэтому используется **b**.
