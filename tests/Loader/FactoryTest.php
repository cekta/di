<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Loader;

use Cekta\DI\Loader\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class FactoryTest extends TestCase
{
    public function testWithoutArgument()
    {
        $name = stdClass::class;
        $loader = new Factory($name);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $this->assertInstanceOf($name, $loader($container));
    }

    public function testWithArguments()
    {
        $obj = new class () {
            public $a;
            public $b;

            public function __construct($a = 1, $b = 2)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $name = get_class($obj);
        $loader = new Factory($name, 'a', 'b');
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap(
                [
                    ['a', 123],
                    ['b', 321]
                ]
            );
        assert($container instanceof ContainerInterface);
        $result = $loader($container);
        $this->assertInstanceOf($name, $result);
        $this->assertSame(123, $result->a);
        $this->assertSame(321, $result->b);
    }
}
