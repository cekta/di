## KeyValue transform
---
В некоторых случаях людям хочется использовать анонимные функции как Service.

Для реализации этих желаний есть специальный метод, который трансформирует любой Closure в Service.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Psr\Container\ContainerInterface;

$providers[] = new KeyValue([
    'type' => 'mysql',
    'host' => '127.0.0.1',
    'dbName' => 'test'
]);
$providers[] = KeyValue::transform([
    'dsn' => function (ContainerInterface $c) {
        // можно вернуть что угодно и создавать как угодно.
        return "{$c->get('type')}:dbname={$c->get('dbName')};host={$c->get('host')}";
    },
    'example' => 'value'
]);
$container = new Container(...$providers);
assert($container->get('dsn') ==='mysql:dbname=test;host=127.0.0.1');
assert($container->get('example') === 'value');
```

Во втором провайдере любая анонимная функция становится сервисом, остальные значения не изменяются.
