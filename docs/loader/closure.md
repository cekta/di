---
parent: Загрузчики
nav_order: 1
---

# Closure

Провайдеры могут возвращать [анонимные функции](https://www.php.net/manual/ru/functions.anonymous.php), 
которые будут вызваны и первым аргументом будет передан объект реализующий **ContainerInterface** который может быть 
использован для получения других зависимостей.  
Все что вернет это анонимная функция и будет считаться результатом.

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

Когда мы запрашиваем зависимость **dsn** container видит что результат анонимная функция, которую он вызывает и 
первым аргументом передает себя для загрузки отсальных зависимостей, то что вернула функция и считается результатом.
