## KeyValue из PHP

/config.php
```php
<?php
return [
    'username' => 'root'
];
```

/index.php
```php
<?php
/** @noinspection PhpIncludeInspection */
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(require __DIR__ . '/config.php');
$container = new Container(...$providers);
assert($container->get('username') === 'root');
```

