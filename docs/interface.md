---
nav_order: 3
---

# Регистрация interface

Классы могут зависеть от интерфейсов, или абстрактных классов и нужно во все места подставлять одну реализацию.

Нужно иметь возможность легко изменять реализацию.

Например:

 * Класс Example зависит от интерфейса для работы с БД (Connection)
 * В проекте может существовать несколько реализаций Connection (A и B две реализации)
 * Каждая из этих реализаций может иметь свои завимости (для упрощения опустим это).

/index.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\MyContainer;

require __DIR__ . '/vendor/autoload.php';

$container = new MyContainer();
$container->get(\Cekta\DI\Example::class);
```

/src/Example.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class Example
{
    public function __construct(Connection $connection)
    {
        echo 'implementation is ' . get_class($connection) . PHP_EOL;
    }
}
```

/src/Connection.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

interface Connection
{

}
```

/src/A.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class A implements Connection
{

}
```

/src/B.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

class B implements Connection
{

}

```

/src/MyContainer.php
```php 
<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\Implementation;

class MyContainer extends Container
{
    public function __construct()
    {
        $providers[] = new Implementation(require __DIR__ . '/../implementation.php');
        $providers[] = new Autowiring(new Reflection());
        parent::__construct(...$providers);
    }
}

```

/implementation.php
```php 
<?php

declare(strict_types=1);

use Cekta\DI\Connection;

return [
    Connection::class => \Cekta\DI\A::class
];
```

Проверим
``` 
$ php -f index.php
implementation is Cekta\DI\A
```

Используя файл implementation.php мы можем регистрировать интерфейсы (или абстракные классы) и их реализацию.  
С помощью этого файла мы можем изменить реализацию во всем приложение.  
Формат php позволяет использовать autocomplete ide и короткие названия.  
Формат может быть любым, но тогда надо писать длинные строки с полным именем класса, без autocomplete.
