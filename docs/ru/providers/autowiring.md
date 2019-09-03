## Autowiring
---
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
## Autowiring и interface
---
В некоторых случаях объект может зависеть от интерфейса к которому может существовать несколько реализаций, тогда вам
надо указать какую надо использовать.

```php
<?php
use Cekta\DI\Loader\Alias;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Container;

class Demo
{
    public $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }
}

interface DriverInterface{}
class FileDriver implements DriverInterface{}
class RedisDriver implements DriverInterface{}

$providers[] = new KeyValue([
    DriverInterface::class => new Alias(FileDriver::class)
]);
$providers[] = new Autowiring();
$container = new  Container(...$providers);
$demo = $container->get(Demo::class);
assert($demo instanceof Demo);
assert($demo->driver instanceof DriverInterface);
assert($demo->driver instanceof FileDriver);
```

В этом примере использовался загрузчик [Alias](../../loaders/alias.md).
## Autowiring и RuleInterface
---
В некоторых случаях, может существовать два класса которые зависят от username, но одному надо username от mysql,
другому от redis.

[RuleInterface](/Provider/Autowiring/RuleInterface.php) позволяет задавать правила для загружаемой зависимости,
чтобы загружать зависимость с другим именем, есть простая реализация в виде [Rule](/Provider/Autowiring/Rule.php).

```php
<?php
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Container;

class DriverMysql
{
    public $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }
}
class DriverRedis
{
    public $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }
}

$providers[] = new KeyValue([
    'username' => 'mysql username',
    'redis.username' => 'redis username'
]);
$providers[] = new Autowiring(new Autowiring\Rule(DriverRedis::class, ['username' => 'redis.username']));
$container = new Container(...$providers);

$mysql = $container->get(DriverMysql::class);
assert($mysql instanceof DriverMysql);
assert($mysql->username === 'mysql username');
$redis = $container->get(DriverRedis::class);
assert($redis instanceof DriverRedis);
assert($redis->username === 'redis username');
```
