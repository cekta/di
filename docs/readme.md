# С чего начать

## Установка используя [composer](https://getcomposer.org/){:target="_blank"}

```
composer require cekta/di
```

## Пример использования

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

Для создания [Container](https://github.com/cekta/di/blob/master/src/Container.php) требуется передать объкты 
реализующие [ProviderInterface](https://github.com/cekta/di/blob/master/src/ProviderInterface.php) их может быть любое 
количество, именно они и занимаются загрузкой зависимостей.  
[Подробней о провайдерах](providers.md)

Когда мы запрашиваем **SomeService::class**, container загружает ее используя 
[Autowiring](provider/autowiring.md), который анализирует аргументы конструктора и понимает что для создания 
требуется PDO::class.  
Загружая **PDO::class**, container использует Autowiring, анализируя аргументы конструтора он видит что для создания 
требуется 4 аргумента (dsn, username, passwd, options).  
Загружая **dsn** container использует KeyValue и предоставляет значение "mysql:dbname=testdb;host=127.0.0.1".  
Подобным образом загружается **username, passwd, options**.  
Все аргументы для создания PDO::class предоставленны и container создаст его.  
Все аргументы для создания SomeService::class предоставленные и container создаст его.  
У созданного объекта может быть вызван любой метод (например bar) который может иметь доступ ко всем 
аргументам конструктора.

Если повторно запрашиваются данные из container, то контейнер их предоставляет не обращаясь провайдерам, а из 
in memory cache для ускорения работы.

### Преимущества

1. [Autowiring](provider/autowiring.md) позволяет загружать зависимости без конфигурации.
2. [KeyValue](provider/key-value.md) позволяет вручную конфигурировать любую зависимость (по необходимости).
3. Неограниченная вложенность зависимостей.
4. Переиспользование контейнеров(один раз объявил другие могут переиспользовать).
5. Возможность кэшировать обращения к 
[ReflectionClass](https://www.php.net/manual/ru/class.reflectionclass.php) 
используя [psr/cache](provider/cache.md) 
и [psr/simple-cache](provider/simple-cache.md)
6. Гибкость в расширение [собственными провайдерами](provider/custom.md).
7. Высокое качества библиотеки (100 code coverage, infection msi 100, статический анализ и тд).
8. В пакете есть все необходимое.
9. Другие преимущества.
