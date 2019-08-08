# Dependency Injection

## Install via [composer](https://getcomposer.org/)

```
composer require cekta/di
```

## Getting Started

/src/Foo.php
```php
<?php
declare(strict_types=1);

use PDO;

class Foo
{
    private $pdo;

    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }

    public function bar()
    {
        // you have access to db via $this->pdo
    }
}
```

/public/index.php
```php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$container = new MyContainer();
$foo = $container->get(Foo::class);
$foo->bar();
```

/src/MyContainer.php
```php
<?php
declare(strict_types=1);

use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;

class MyContainer extends Container
{
    public function __construct() 
    {
        $providers = [];
        $providers[] = new KeyValue([
            "dsn" => "mysql:dbname=testdb;host=127.0.0.1",
            "username" => "root",
            "passwd" => "secret",
            "options" => []
        ]);
        $providers[] = new Autowiring();
        parent::__construct(...$providers);
    }
}
```

## Documentation

1. [RU](doc/ru.md)
