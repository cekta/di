## KeyValue из json

/config.json
```json
{
  "username": "root"
}
```

/index.php
```php
<?php
/** @noinspection PhpComposerExtensionStubsInspection */
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(json_decode(file_get_contents(__DIR__ . '/config.json'), true));
$container = new Container(...$providers);
assert($container->get('username') === 'root');
```

ext-json required
