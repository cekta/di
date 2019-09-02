## Использования класса
---
Мы наследуемся от Container и переопределяем метод конструктора создавая нужные провайдеры 
и передавая их в конструктор родителя.

/src/MyContainer.php
```php
<?php
namespace Vendor\Package;

use Cekta\DI\Container;

class MyContainer extends Container
{
    public function __construct() 
    {
        $providers = [];
        // Тут создаем провайдеры
        parent::__construct(...$providers);
    }
};
```

/public/index.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

$container = new Vendor\Package\MyContainer();
```

/cli.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

$container = new Vendor\Package\MyContainer();
```
