# Dependency Injection

## Install via [composer](https://getcomposer.org/)

```
composer require cekta/di
```

## Getting Started

/app/config.json
```json
{
  "dsn": "mysql:dbname=testdb;host=127.0.0.1",
  "username": "root",
  "passwd": "secret",
  "options": {}
}
```

/app/di.php
```php
<?php
declare(strict_types=1);

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Container;

$providers = [];
$providers[] = new KeyValue(json_decode(file_get_contents(__DIR__ . '/config.json'), true));
$providers[] = new Autowiring();
return new Container(...$providers);
```

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

use Psr\Container\ContainerInterface;

require __DIR__ . '/../vendor/autoload.php';
/** @var ContainerInterface $container */
$container = require __DIR__ . '/app/di.php';

$foo = $container->get(Foo::class);
$foo->bar();
```

## Documentation

1. [RU](doc/ru.md)
