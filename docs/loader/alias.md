---
parent: Загрузчики
nav_order: 2
---

# Alias

Для создания загрузчика необходимо передать имя зависимости, которая будет в действительности загруженно.

Alias очень удобен чтобы задавать реализации для интерфейсов.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Loader\Alias;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue([
    'a' => new Alias('b'),
    'b' => 'value b'
]);
$container = new Container(...$providers);
assert($container->get('a') === 'value b')
```

Когда мы пытаемся загрузить зависимость с именем 'a' в действительно мы обращаемся к зависимости 'b' и возвращаем ее 
результат.
