---
layout: default
parent: Провайдеры
nav_order: 1
---

# KeyValue

{: .no_toc }

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
## KeyValue из environment

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

$providers[] = new KeyValue(getenv());
$container = new Container(... $providers);
echo $container->get('PATH');
```
## KeyValue из json

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
## KeyValue из PHP

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

## KeyValue из произвольного формата

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
## KeyValue return LoaderInterface

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

В этом примере использовался загрузчик [Service](../../loaders/service.md).
## KeyValue closureToService

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
$providers[] = KeyValue::closureToService([
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
## Autowiring и производительность
---
Для получения аргументов конструктора, используется [Reflection](https://www.php.net/manual/ru/book.reflection.php).

Reflection в PHP не слишком быстрый, существуют провайдеры позволяющие кэшировать обращения к
Reflection используя [psr/cache](https://www.php-fig.org/psr/psr-6/) и
[psr/simple-cache](https://www.php-fig.org/psr/psr-16/).
