# Cekta\DI

## Как работает Container.

Cekta\DI\Container реализует \Psr\Container\ContainerInterface который предоставляет возможность:

1. Получить зависимость по id (get)
2. Проверить может ли быть загружена зависимость по id (has)

Для первого получения зависимости Container использует различные объекты реализующие Cekta\DI\ProviderInterface 
которые передаются в конструкторе при создание Container. 
В начале находится провайдер который может предоставить зависимость (canProvide), а потом провайдер 
получает зависимость (provide). 
Container запоминает результат чтобы в дальнейшем ее получать минуя обращения к провайдерам.

Если два провайдера могут предоставить зависимость с одинаковым id, то будет использоватся тот что передан раньше 
в Container.

Provider может предоставить объект реализующий Cekta\DI\LoaderInterface, это значит что для получения зависимости, 
необходимы необходимы другие зависимости.

Например для создания \PDO нужно знать dsn, username, passwd, options. 
Чтобы создать dsn строку подключения нужно знать тип базы(type), имя базы(dbname), ее адрес (host) и так далее.
Разрешение всех необходимых зависимостей, вложенность которых может быть произвольной это задача Container, он их 
загружает по мере необходимости.

Расширение возможностей Container, осуществляется через создание собственных ProviderInterface или LoaderInterface.

В комплетке с библиотекой идет необходимый набор провайдеров и загрузчиков которые помогут реализовать все что угодно.

## Стандартные провайдеры

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
1. Провайдеров можно задвать сколько угодно.
2. Значения в KeyValue могут быть любым типом.
3. В случае если 2 провайдера предоставляют одну и туже зависимость используется тот что передан раньше.

Источник данных для провайдера может быть что угодно.

#### KeyValue из environment

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(getenv());
$container = new Container(... $providers);
echo $container->get('PATH');
```

#### KeyValue из json

/config.json
```json
{
  "username": "root"
}
```

/index.php
```php
<?php
/** @noinspection PhpComposerExtensionStubsInspection */
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(json_decode(file_get_contents(__DIR__ . '/config.json'), true));
$container = new Container(...$providers);
assert($container->get('username') === 'root');
```

ext-json required

#### KeyValue из PHP.

/config.php
```php
<?php
return [
    'username' => 'root'
];
```

/index.php
```php
<?php
/** @noinspection PhpIncludeInspection */
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(require __DIR__ . '/config.php');
$container = new Container(...$providers);
assert($container->get('username') === 'root');
```

#### KeyValue из произвольного формата

В мире существует огромное число различных форматов и вы можете использовать любой к которому у вас есть парсер.

Например для загрузки из YAML

Установим yaml parser
```
composer require symfony/yaml
```

/config.yaml
```yaml
username: root
```

/index.php
```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Symfony\Component\Yaml\Yaml;

$providers[] = new KeyValue(Yaml::parseFile(__DIR__ . '/config.yaml'));
$container = new Container(...$providers);
assert($container->get('username') === 'root');
```

#### KeyValue return LoaderInterface.

В некоторых случаях для загрузки зависимости могут потребоваться другие зависимости.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Loader\Service;
use Cekta\DI\Provider\KeyValue;
use Psr\Container\ContainerInterface;

$providers[] = new KeyValue([
    'type' => 'mysql',
    'host' => '127.0.0.1',
    'dbName' => 'test'
]);
$providers[] = new KeyValue([
    'dsn' => new Service(function (ContainerInterface $c) {
        return "{$c->get('type')}:dbname={$c->get('dbName')};host={$c->get('host')}";
    })
]);
$container = new Container(...$providers);
assert($container->get('dsn') ==='mysql:dbname=test;host=127.0.0.1');
```

В этом примере использовался загрузчик [Service](#service).

#### KeyValue transform.

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

Этот провайдер является декаратором, который перед использование Reflection пытается найти значение в 
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

## Загручики

Если для разрешения одной зависимости требуются другие, то provider возвращает объект реализующий 
[LoaderInterface](/src/LoaderInterface.php).

[Container](/src/Container.php) получив такое от провайдера, передает себя для того чтобы загрузить нужные зависимости.

### Service

Этот загрузчик принимает на входе анонимную функцию, первым аргументом которой он передает 
Psr\Container\ContainerInterface, то что вернет эта функция это и будет результатом.

Пример кода смотри в [KeyValue может возвращать LoaderInterface](#keyvalue-return-loaderinterface)

### Alias

Этот провайдер получает id зависимости и обращается к ней в момент загрузки.

Этот провадер удобно использовать для регистрации интерфейсов и зависимостей их реализующих.

[Пример использования](#autowiring-and-interface)

## Практические советы

В этом разделе я решил собрать советы которые сам использую и рекомендовал бы вам, чтобы было проще работать с библиотекой.

### Создание объекта Container

Самое первое с чем сталкиваешься это как создавать объект контейнера чтобы его можно было переиспользовать, например 
есть обработчик HTTP запросов и запускалка CLI команд, в обоих случаях нужен один и тот же Container.

Два основных способа опишу ниже.

#### Использования класса

Мы наследуемся от Container и переопределяем метод конструктора создавая нужные провайдеры 
и передавая их в конструктор родителя.

/src/MyContainer.php
```php
<?php
namespace Vendor\Package;

use Cekta\DI\Container;

class MyContainer extends Container
{
    public function __construct() 
    {
        $providers = [];
        // Тут создаем провайдеры
        parent::__construct(...$providers);
    }
};
```

/public/index.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

$container = new Vendor\Package\MyContainer();
```

/cli.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

$container = new Vendor\Package\MyContainer();
```

#### Использование файла

Другой вариант это вынести создание Container в отдельный и возвращать объект, подключая его по необходимости.

/app/container.php
```php
<?php
use Cekta\DI\Container;

$providers = [];
// Тут создаем провайдеры
return new Container(...$providers)
```

/public/index.php
```php
<?php
/** @noinspection PhpIncludeInspection */

use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require '../app/container.php';
```

/cli.php
```php
<?php
/** @noinspection PhpIncludeInspection */

use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require 'app/container.php';
```

### Регистрируйте реализации интерфейсов в одном месте.

Обычно в любом проекте есть интерфейсы, где нужно указывать реализации используемые вами, я рекомендую такое место 
сделать в одном месте.

```php
<?php
/** @noinspection PhpIncludeInspection */

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\KeyValue;

interface Logger;
class FileLogger implements Logger;
class StdoutLogger implements Logger;

$providers[] = new KeyValue(require '../app/implementation.php');
$providers[] = new Autowiring();
```

/app/implementation.php
```php
<?php
/** @noinspection PhpUndefinedClassInspection */

return [
    Logger::class => new Alias(StdoutLogger::class)
];
```

В файле implementation.php можно таким образом указывать все существующие интерфейсы и их реализации, в случае если 
потребуется что то изменить вы всегда можете открыть этот файл и поменять не трогая остальных мест.

### Используй autocomplete

Для autocomplete в PHPSTORM я использую [php di plugin](https://plugins.jetbrains.com/plugin/7694-php-di-plugin/)
который помогает делать авто комплит если я запрашиваю классы или интерфейсы у container.

## Как связать

Процесс разработки этой библиотеки полностью транслировался на youtube, 
[ссылка на плейлист](https://www.youtube.com/playlist?list=PL7Nh93imVuXyePa8PjJ1qZzkjkGFWyDZ0)

Есть чат для youtube канала и где можно задать вопрос по библиотеке [telegram](https://t.me/dev_ru)

[Мой телеграмм](https://t.me/KuvshinovEE)

Я буду очень рад вашим Pull Request, а также оставленными сообщениями об ошибках или пожеланиям по улучшениям.
