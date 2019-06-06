--TEST--
Набор параметров ключ значение
--FILE--
<?php

use Cekta\DI\Container;
use Cekta\DI\Loader\Alias;

require __DIR__ . '/../../vendor/autoload.php';

$container = new Container([
    'a' => 'a class',
    'i' => new Alias('a')
]);
echo $container->get('i');
?>
--EXPECT--
a class
