---
parent: Провайдеры
nav_order: 2
title: Autowiring
---

# Навигация по странице
{: .no_toc }

1. TOC
{:toc}

# Autowiring
{: .no_toc }

Этот провайдер занимает загрузкой объекта по полному имени класса ([FQCN](https://lmgtfy.com/?q=php+fqcn)).

Используя Reflection он пытается найти конструктор и посмотреть какие аргументы ему требуется передать для создания.  
Если у аргумента указан тип (отличный от примитивных int, string, array и тд) то он пытается передать этот объект.  
Если тип не указан то он использует имя переменной без $.  
Значения по умолчанию для аргументов никак не учитываются.

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

Можно обращаться к стандартным классам php, например PDO.

## Autowiring и interface

В некоторых случаях объект может зависеть от интерфейса к которому может существовать несколько реализаций.  
Каждому интерфейсу необходимо указать какой именно класс его реализует с помощью [KeyValue](key-value.md)

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

## Autowiring и RuleInterface

[RuleInterface](https://github.com/cekta/di/blob/master/src/Provider/Autowiring/RuleInterface.php) позволяет 
переопределять зависимости полученные автоматически, правила можно задавать как для конкретного класса, так и для 
целого пакеты со всеми его классами.  
Простейщий пример реализации [Rule](https://github.com/cekta/di/blob/master/src/Provider/Autowiring/Rule.php).

В некоторых случаях, может существовать два класса которые зависят от username, но одному надо username от mysql,
другому от redis.

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

## Autowiring и производительность

Reflection в PHP не слишком быстрый, существуют провайдеры позволяющие кэшировать обращения к
Reflection используя 
[psr/simple-cache](https://www.php-fig.org/psr/psr-16/) и [psr/cache](https://www.php-fig.org/psr/psr-6/)
это может существенно ускорить производительность на production.
