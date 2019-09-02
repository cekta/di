## KeyValue из environment
---
```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(getenv());
$container = new Container(... $providers);
echo $container->get('PATH');
```
