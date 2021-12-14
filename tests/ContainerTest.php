<?php

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class ContainerTest extends TestCase
{
    public function testGet(): void
    {
        $obj = new class () {
            public string $magic;
            public string $foo;

            public function __construct(string $magic = 'test', string $foo = 'test2')
            {
                $this->magic = $magic;
                $this->foo = $foo;
            }
        };
        $class = get_class($obj);
        $expect = 'value';
        $container = new Container([
            'test' => $expect
        ], [
            'magic' => 'test'
        ], [
            'foo' => function (ContainerInterface $container) {
                $this->assertSame(Container::class, get_class($container));
                return 'foo value';
            }
        ]);
        $result = $container->get($class);
        $this->assertSame($class, get_class($result));
        $this->assertSame($expect, $result->magic);
        $this->assertSame('foo value', $result->foo);

        $this->assertTrue($container->has('magic'));
        $this->assertSame($expect, $container->get('magic'));

        $this->assertTrue($container->has('test'));
        $this->assertSame($expect, $container->get('test'));

        $this->assertTrue($container->has('foo'));
        $this->assertSame('foo value', $container->get('foo'));
    }

    public function testHasInvalidName(): void
    {
        $container = new Container();
        $this->assertFalse($container->has('invalid name'));
    }
}
