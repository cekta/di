#### KeyValue из environment

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(getenv());
$container = new Container(... $providers);
echo $container->get('PATH');
```
---
* [KeyValue](key-value.md)
* [JSON](json.md)
* [PHP](PHP.md)
* [Custom format](custom-format.md)
* [LoaderInterface](loader-interface.md)
* [ClosureToService](closureToService.md)
---
[Вернуться на главную](../../readme.md)
