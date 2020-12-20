<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Strategy\Definition;

use Cekta\DI\Strategy\Definition\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FactoryTest extends TestCase
{
    public function testWithArguments(): void
    {
        $obj = new class () {
            /**
             * @var int
             */
            public $a;
            /**
             * @var int
             */
            public $b;

            public function __construct(int $a = 1, int $b = 2)
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
