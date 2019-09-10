# Getting started with Cekta/DI

## Install via [composer](https://getcomposer.org/){:target="_blank"} 

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
[\Psr\Container\ContainerInterface](https://www.php-fig.org/psr/psr-11/){:target="_blank"} 
который предоставляет возможность:

1. Получить зависимость по id (get)
2. Проверить может ли быть загружена зависимость по id (has)

При создание Container ему необходимо передать любое количество провайдеров, которые будут использованны для получения 
зависимостей.

Когда вы пытаетесь загрузить зависимость, Container в начале пытается определить провайдер который может ее загрузить.  
Для этого он опрашивает провайдеры по порядку спрашивая может ли он загрузить требуемую зависимость (canProvide).  

Определив провайдер ответственный за загрузку зависимости он используя его загружает зависимость (provide).

Провайдер может вернуть объект реализующий 
[LoaderInterface](https://github.com/cekta/di/blob/master/src/LoaderInterface.php) это значит что для получения 
зависимости могут потребоваться другие зависимости.  
Для этого Container вызывает метод __invoke у загрузчика и передает себя, чтобы использовали его для разрешения 
дочерних зависимостей.

Container сохраняет полученный результат в in memory чтобы в последующем возвращать результат от туда, минуя обращения 
к провайдерам и загрузчикам.

Если два провайдера могут предоставить зависимость с одинаковым id, то будет использоваться тот что передан раньше 
в Container.

Например для создания \PDO нужно знать dsn, username, passwd, options. 
Чтобы создать dsn строку подключения нужно знать тип базы(type), имя базы(dbname), ее адрес (host) и так далее.
Разрешение всех необходимых зависимостей, вложенность которых может быть произвольной это задача Container, он их 
загружает по мере необходимости.

Расширение возможностей Container, осуществляется через создание собственных [провайдеров](provider/custom.md) или 
[загрузчиков](loader/custom.md).

В комплекте с библиотекой идет необходимый набор провайдеров и загрузчиков которые помогут реализовать все что угодно.  
Нужно просто посмотреть документацию с примерами.
