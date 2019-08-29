
#### KeyValue return LoaderInterface.

В некоторых случаях для загрузки зависимости могут потребоваться другие зависимости.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Loader\Service;
use Cekta\DI\Provider\KeyValue;
use Psr\Container\ContainerInterface;

$providers[] = new KeyValue([
    'type' => 'mysql',
    'host' => '127.0.0.1',
    'dbName' => 'test'
]);
$providers[] = new KeyValue([
    'dsn' => new Service(function (ContainerInterface $c) {
        return "{$c->get('type')}:dbname={$c->get('dbName')};host={$c->get('host')}";
    })
]);
$container = new Container(...$providers);
assert($container->get('dsn') ==='mysql:dbname=test;host=127.0.0.1');
```

В этом примере использовался загрузчик [Service](../../loaders/service.md).

---
* [KeyValue](key-value.md)
* [Environment](environment.md)
* [JSON](json.md)
* [PHP](PHP.md)
* [Custom format](custom-format.md)
* [Transform](transform.md)
---
[Вернуться на главную](../../readme.md)
