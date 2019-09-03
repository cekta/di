### KeyValue

Этот провайдер представляет из себя массив ключ => значение.
Значением может быть что угодно.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers = [];
$providers[] = new KeyValue([
    'password' => 'top secret'
]);
$providers[] = new KeyValue([
    'username' => 'root',
    'password' => 'public',
    stdClass::class => new stdClass(),
]);
$container = new Container(...$providers);
assert($container->get('username') === 'root');
assert($container->get('password') === 'top secret');
assert($container->get(stdClass::class) instanceof stdClass);
```

Выводы из примера:
1. Провайдеров можно задавать сколько угодно.
2. Значения в KeyValue могут быть любым типом.
3. В случае если 2 провайдера предоставляют одну и туже зависимость используется тот что передан раньше.

Источник данных для провайдера может быть что угодно.

---
* [Environment](environment.md)
* [JSON](json.md)
* [PHP](PHP.md)
* [Custom format](custom-format.md)
* [LoaderInterface](loader-interface.md)
* [ClosureToService](closureToService.md)
---
[Вернуться на главную](../../readme.md)
