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

#### Autowiring and interface.

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

В этом примере использовался загрузчик [Alias](#alias).

#### Autowiring и RuleInterface

В некоторых случаях, может существовать два класса которые зависят от username, но одному надо username от mysql,
другому от redis.

[RuleInterface](/src/Provider/Autowiring/RuleInterface.php) позволяет задавать правила для загружаемой зависимости,
чтобы загружать зависимость с другим именем, есть простая реализация в виде [Rule](/src/Provider/Autowiring/Rule.php).

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

#### Autowiring и производительность

Для получения аргументов конструктора, используется [Reflection](https://www.php.net/manual/ru/book.reflection.php).

Reflection в PHP не слишком быстрый, существуют провайдеры позволяющие кэшировать обращения к
Reflection используя [psr/cache](https://www.php-fig.org/psr/psr-6/) и
[psr/simple-cache](https://www.php-fig.org/psr/psr-16/).

### AutowiringSimpleCache

Этот провайдер является декоратором, который перед использование Reflection пытается найти значение в
[psr/simple-cache](https://www.php-fig.org/psr/psr-16/) на продакшене каждое обращение может браться из кэша.

1. Выберите реализацию
[psr/simple-cache-implementation](https://packagist.org/providers/psr/simple-cache-implementation)
или создайте свою
2. Установите реализацию например [cache/array-adapter](https://packagist.org/packages/cache/array-adapter)
этот вариант прост для демонстрации, но для production хорошо чтобы он кэшировал в постоянное хранилище (redis,
memcached, file system и тд).
    ```
    composer require cache/array-adapter
    ```
3. Пример

```php
<?php
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cekta\DI\Container;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\AutowiringSimpleCache;

$cache = new ArrayCachePool();
$providers[] = new AutowiringSimpleCache($cache, new Autowiring());
$container = new Container(... $providers);

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result используя Reflection и помещает в кэш" . PHP_EOL;

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result последующие вызовы идут минуя Provider и Reflection" . PHP_EOL;

$container = new Container(...$providers);

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result минуя Reflection используя Cache" . PHP_EOL;

$start = microtime(true);
$container->get(stdClass::class);
$result = number_format(microtime(true) - $start, 17);
echo "$result последующие вызовы идут минуя Provider и Reflection" . PHP_EOL;
```

Output:
```
0.00098490715026856 используя Reflection и помещает в кэш
0.00000500679016113 последующие вызовы идут минуя Provider и Reflection
0.00007414817810059 минуя Reflection используя Cache
0.00000405311584473 последующие вызовы идут минуя Provider и Reflection
```

Вывод времени и microtime не совсем корректный bencmark показывающий разницу, но для примера сойдет.

### AutowiringCache

Этот провайдер использует для кэширования [psr/cache](https://www.php-fig.org/psr/psr-6/) в остальном он похож на
AutowiringSimpleCache

---
[Вернуться на главную.](../README.md)
