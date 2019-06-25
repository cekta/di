--TEST--
Autowire проверка на вызов с аргументами разных типов и без типов
--FILE--
<?php

use Cekta\DI\Container;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\KeyValue\Loader\Service;

require __DIR__ . '/../../vendor/autoload.php';

$providers[] = new KeyValue([
    'a' => new Service(function () {
        return new stdClass;
    })
]);
$container = new Container(...$providers);
$a1 = $container->get('a');
$a2 = $container->get('a');

var_dump($a1 === $a2);
?>
--EXPECT--
bool(true)
