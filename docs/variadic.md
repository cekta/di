---
nav_order: 1
---

# Autowiring variadic

В [php >= 5.6](https://www.php.net/manual/ru/migration56.new-features.php) 
появились variadic аргументы методов.

Большинство библиотек внедрения зависимостей 
[не поддерживает autowiring в variadic](https://github.com/PHP-DI/PHP-DI/issues/619), но не эта библиотека.

Например есть классы Foo и Bar требующие динамическое число аргументов.  
При этом класс Bar еще хочет внедрить зависимость с другим именем (смотри анотация **@inject**).

/src/Foo.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class Foo
{

    public function __construct(A ...$test)
    {
        var_dump($test);
    }
}
```

/src/Bar.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class Bar
{
    /**
     * Example constructor.
     * @param A ...$test
     * @inject magic $test
     */
    public function __construct(A ...$test)
    {
        var_dump($test);
    }
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
        $providers[] = new KeyValue(['test' => [new A(), new A()], 'magic' => []]);
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
$container->get(\Cekta\DI\Foo::class);
$container->get(\Cekta\DI\Bar::class);
```

Результат работы
```
$ php public/index.php 
/src/Foo.php:11:
array(2) {
  [0] =>
  class Cekta\DI\A#4 (0) {
  }
  [1] =>
  class Cekta\DI\A#5 (0) {
  }
}
/src/Bar.php:16:
array(0) {
}
```

Как определяется имя зависимости для variadic аргумента:
1. Если указана анотация @inject для аргумента, то используется ее значения.  
    Для класса **Bar** анотации @inject указано использовать **magic**.  
    Мы видим что у класса Bar пустой массив в выводе.
2. Используется имя аргумента, без символа $
    Для класса **Foo** используется зависимость с именем аргумента **test**.  
    В примере у Foo в качестве аргумента приходит массив с 2 элементами
