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
declare(strict_types=1);

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
declare(strict_types=1);

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

#### KeyValue может возвращать LoaderInterface.

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

В этом примере использовался загрузчик Service.

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

Можно таким образом создавать в том числе и классы предоставляемые php, например PDO.

#### Autowiring и интерфейсы.

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

В этом примере использовался Loader Alias.
