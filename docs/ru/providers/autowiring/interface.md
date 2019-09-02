## Autowiring и interface
---
В некоторых случаях объект может зависеть от интерфейса к которому может существовать несколько реализаций, тогда вам
надо указать какую надо использовать.

```php
<?php
use Cekta\DI\Loader\Alias;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Container;

class Demo
{
    public $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }
}

interface DriverInterface{}
class FileDriver implements DriverInterface{}
class RedisDriver implements DriverInterface{}

$providers[] = new KeyValue([
    DriverInterface::class => new Alias(FileDriver::class)
]);
$providers[] = new Autowiring();
$container = new  Container(...$providers);
$demo = $container->get(Demo::class);
assert($demo instanceof Demo);
assert($demo->driver instanceof DriverInterface);
assert($demo->driver instanceof FileDriver);
```

В этом примере использовался загрузчик [Alias](../../loaders/alias.md).
