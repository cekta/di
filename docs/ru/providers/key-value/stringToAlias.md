## KeyValue stringToAlias

В некоторых случаях людям хочется вынести список все используемых интерфейсов и их реализаций в отдельный файлик, 

Для реализаций этих потребностей есть специальный метод хелпер.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Psr\Container\ContainerInterface;

interface SomeInterface{}

class SomeImplementation implements SomeInterface{}

$providers[] = KeyValue::closureToService([
    SomeInterface::class => SomeImplementation::class,
    'example' => 123
]);
$container = new Container(...$providers);
assert($container->get(SomeInterface::class) instanceof SomeImplementation);
assert($container->get('example') === 123);
```

Во втором провайдере любая строка становится Alias, остальные значения не изменяются.
