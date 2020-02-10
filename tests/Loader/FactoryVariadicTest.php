<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Loader;

use Cekta\DI\Loader\FactoryVariadic;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class FactoryVariadicTest extends TestCase
{

    public function testInvoke(): void
    {
        $obj = new class (1) {
            public $variadic;
            public $a;

            public function __construct(int $a, stdClass ...$variadic)
            {
                $this->a = $a;
                $this->variadic = $variadic;
            }
        };
        $name = get_class($obj);
        $loader = new FactoryVariadic($name, 'a', 'variadic');
        $variadic = [new stdClass(), new stdClass()];
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                ['a', 123],
                ['variadic', $variadic],
            ]
        );
        assert($container instanceof ContainerInterface);
        $result = $loader($container);
        $this->assertSame($variadic, $result->variadic);
        $this->assertSame(123, $result->a);
    }

    public function testInvokeWithoutArguments()
    {
        $obj = new class () {
            public $classes;

            public function __construct(stdClass ...$classes)
            {
                $this->classes = $classes;
            }
        };
        $name = get_class($obj);
        $loader = new FactoryVariadic($name);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $result = $loader($container);
        $this->assertInstanceOf($name, $result);
        $this->assertEmpty($result->classes);
    }

    public function testInvokeWithVariadicNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new class () {
            public $variadic;

            public function __construct(stdClass ...$variadic)
            {
                $this->variadic = $variadic;
            }
        };
        $name = get_class($obj);
        $loader = new FactoryVariadic($name, 'variadic');
        $variadic = new stdClass();
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with('variadic')->willReturn($variadic);
        assert($container instanceof ContainerInterface);
        $loader($container);
    }
}
