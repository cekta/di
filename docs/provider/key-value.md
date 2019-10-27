---
layout: default
parent: Провайдеры
title: KeyValue
nav_order: 1
---

# Навигация по странице
{: .no_toc }

1. TOC
{:toc}

# KeyValue
{: .no_toc }

Этот провайдер представляет из себя массив ключ => значение, значением может быть что угодно.

Массив задается при создание провайдера.

Этот провайдер очень удобно использовать для загрузки различных параметров, которые могут изменятся в различных 
окружениях.  
В особых случаях можно вручную сконфигурировать как должна создаваться зависимость, но обычно используется 
автоматическая конфигурация с использованием [Autowiring](autowiring.md).

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
$container = new Container(...$providers);
echo $container->get('PATH');
```

Не забывайте что в переменных окружения могут быть только строки и значения вроде 'true' надо конвертировать в 
bool(true) в подобных случаях.

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

## KeyValue и LoaderInterface

В некоторых случаях для создания зависимости могут потребоваться другие зависимости, например чтобы создать 
dsn строку подключения надо знать: тип, имя, адрес хоста и тд.  
Провайдер может вернуть [анонимную функцию](https://www.php.net/manual/ru/functions.anonymous.php) или объект 
реализующий [LoaderInterface](https://github.com/cekta/di/blob/master/src/LoaderInterface.php)
тогда это значение будет загружено и в процессе загрузки оно будет иметь доступ к container для получения необходимых 
зависимостей.

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
$providers[] = new KeyValue([
    'dsn' => function (ContainerInterface $c) {
        return "{$c->get('type')}:dbname={$c->get('dbName')};host={$c->get('host')}";
    }
]);
$container = new Container(...$providers);
assert($container->get('dsn') ==='mysql:dbname=test;host=127.0.0.1');
```

[Другие загрузчики](../loaders.md)

## Метод stringToAlias

stringToAlias это статический метод который во входящем массиве заменяет строки на 
[загрузчик Alias](../loader/alias.md) и возвращает **KeyValue**.

```php
<?php
use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;

interface SomeInterface{}

class SomeImplementation implements SomeInterface{}

$providers[] = KeyValue::stringToAlias([
    SomeInterface::class => SomeImplementation::class,
    'example' => 123
]);
$container = new Container(...$providers);
assert($container->get(SomeInterface::class) instanceof SomeImplementation);
assert($container->get('example') === 123);
```

Это может удобно в случае когда мы хотим в одном месте(например файле implemetation.php) хранить интерфейсы 
и их реализации и изменять их.
