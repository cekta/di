--TEST--
Круговые зависимости с бесконечным разрешением
--FILE--
<?php

use Cekta\DI\Container;
use Cekta\DI\Provider\Autowire;
use Psr\Container\ContainerExceptionInterface;

require __DIR__ . '/../../vendor/autoload.php';

class FooA
{
    public function __construct(FooB $b)
    {
    }
}

class FooB
{
    public function __construct(FooA $a)
    {
    }
}

$providers[] = new Autowire();
$container = new Container(...$providers);
try {
    $container->get(FooA::class);
} catch (ContainerExceptionInterface $exception) {
    var_dump($exception->getMessage());
}

?>
--EXPECT--
string(50) "Infinite recursion for `FooA`, calls: `FooA, FooB`"
