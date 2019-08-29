### Autowiring

Этот провайдер занимает загрузкой объекта по полному имени класса ([FQCN](https://lmgtfy.com/?q=php+fqcn)).

Если у класса есть конструктор который принимает аргументы, то провайдер их предоставляет.
ID для зависимости он берет на основе типа (если он указан и это не int, string, array, bool), если тип не указан то
используется имя аргумента, значение по умолчанию никак не учитывается.

```php
<?php
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Container;

class Magic
{
    public $class;
    public $number;
    public $default;

    public function __construct(stdClass $class, int $number, $default = 1)
    {
        $this->class = $class;
        $this->number = $number;
        $this->default = $default;
    }
}

$obj = new stdClass;
$obj->foo = 567;
$providers[] = new KeyValue([
    stdClass::class => $obj,
    'number' => 123,
    'default' => 789
]);
$providers[] = new Autowiring();
$container = new Container(...$providers);

$magic = $container->get(Magic::class);
assert($magic instanceof Magic);
assert($magic->class instanceof stdClass);
assert($magic->class->foo === 567);
assert($magic->number === 123);
assert($magic->default === 789);
```

Можно обращаться в том числе и классы предоставляемые php, например PDO.

---
* [Autowiring и interface](interface.md) 
* [Autowiring и RuleInterface](rule-interface.md) 
* [Autowiring и производительность](perfomance.md) 
* [AutowiringSimpleCache](simple-cache.md) 
* [AutowiringCache](cache.md) 
---
[Вернуться на главную](../../readme.md)
