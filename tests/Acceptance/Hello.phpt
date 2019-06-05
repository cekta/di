--TEST--
Набор параметров ключ значение
--FILE--
<?php

use Cekta\DI\Container;

require __DIR__ . '/../../vendor/autoload.php';

$container = new Container([
    'username' => 'root',
    'password' => 12345
]);
echo $container->get('username');
?>
--EXPECT--
root
