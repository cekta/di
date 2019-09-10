# Getting started with Cekta/DI

## Install via [composer](https://getcomposer.org/)

```
composer require cekta/di
```

## Basic usage 

```php
<?php
/** @noinspection PhpComposerExtensionStubsInspection */

use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\Autowiring;

class SomeService
{
    private $pdo;

    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }

    public function bar()
    {
        // you have access to db via $this->pdo
    }
}

$providers[] = new KeyValue([
    "dsn" => "mysql:dbname=testdb;host=127.0.0.1",
    "username" => "root",
    "passwd" => "secret",
    "options" => []
]);
$providers[] = new Autowiring();
$container = new Container(...$providers);
$service = $container->get(SomeService::class);
assert($service instanceof SomeService);
$service->bar();
```

## Как работает Container

[Cekta\DI\Container](https://github.com/cekta/di/blob/master/src/Container.php) 
реализует 
[\Psr\Container\ContainerInterface](https://www.php-fig.org/psr/psr-11/){:target="_blank" } 
который предоставляет возможность:

1. Получить зависимость по id (get)
2. Проверить может ли быть загружена зависимость по id (has)

Для первого получения зависимости Container использует различные объекты реализующие 
[Cekta\DI\ProviderInterface](https://github.com/cekta/di/blob/master/src/ProviderExceptionInterface.php) которые
передаются в конструкторе при создание Container.  
В начале находится провайдер который может предоставить зависимость (canProvide), а потом провайдер получает 
зависимость (provide).  
Container запоминает результат чтобы в дальнейшем ее получать минуя обращения к провайдерам.

Если два провайдера могут предоставить зависимость с одинаковым id, то будет использоваться тот что передан раньше 
в Container.

Provider может предоставить объект реализующий Cekta\DI\LoaderInterface, это значит что для получения зависимости, 
необходимы другие зависимости.

Например для создания \PDO нужно знать dsn, username, passwd, options. 
Чтобы создать dsn строку подключения нужно знать тип базы(type), имя базы(dbname), ее адрес (host) и так далее.
Разрешение всех необходимых зависимостей, вложенность которых может быть произвольной это задача Container, он их 
загружает по мере необходимости.

Расширение возможностей Container, осуществляется через создание собственных ProviderInterface или LoaderInterface.

В комплекте с библиотекой идет необходимый набор провайдеров и загрузчиков которые помогут реализовать все что угодно.
