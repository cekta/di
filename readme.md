# Dependency Injection

## Install via [composer](https://getcomposer.org/)

```
composer require cekta/di
```

## Getting Started

```php
<?php
/** @noinspection PhpComposerExtensionStubsInspection */

use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;

class SomeService
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

$providers[] = new KeyValue([
    "dsn" => "mysql:dbname=testdb;host=127.0.0.1",
    "username" => "root",
    "passwd" => "secret",
    "options" => []
]);
$providers[] = new Autowiring();
$container = new Container(...$providers);
$service = $container->get(SomeService::class);
assert($service instanceof SomeService);
$service->bar();
```

## Documentation

1. [RU](docs/ru/readme.md)
