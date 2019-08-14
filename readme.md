# Dependency Injection Container

## Install via [composer](https://getcomposer.org/)

```
composer require cekta/di
```

## Getting Started

```php
<?php

// Require composer autoload file
require_once './vendor/autoload.php';

use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;

$definitions = [
    'dsn' => 'mysql:dbname=testdb;host=127.0.0.1',
    'username' => 'root',
    'passwd' => 'secret',
    'options' => []
];

$key_value = new KeyValue($definitions);

$autowire = new Autowiring();

$providers = [$key_value, $autowire];

// Create Container instance with two providers
$container = new Container(...$providers);

// Example usage:
class SomeService
{
    private $pdo;

    /**
     * Dependency PDO have been autowired
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function bar()
    {
        // you have access to db via $this->pdo
    }
}

// Will be returned instance of SomeService::class
$service = $container->get(SomeService::class);

$service->bar();

```

## Documentation

* [Russian documentation](doc/ru.md)
* English in progress
