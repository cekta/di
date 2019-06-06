--TEST--
Сложные зависимости
--FILE--
<?php

use Cekta\DI\Container;
use Cekta\DI\Test\Database;
use Psr\Container\ContainerInterface;

require __DIR__ . '/../../vendor/autoload.php';
$container = new Container([
    'name' => 'magic',
    'username' => 'root',
    'password' => '12345',
    'host' => '127.0.0.1',
    'type' => 'mysql',
    'options' => [],
    'dsn' => function (ContainerInterface $container) {
        return "{$container->get('type')}:dbname={$container->get('name')};host={$container->get('host')}";
    },
    Database::class => function (ContainerInterface $container) {
        return new Database(
            $container->get('dsn'),
            $container->get('username'),
            $container->get('password'),
            $container->get('options')
        );
    }
]);
$db = $container->get(Database::class);
echo "$db->dsn $db->username $db->password". PHP_EOL;
?>
--EXPECT--
mysql:dbname=magic;host=127.0.0.1 root 12345
