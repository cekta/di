<?php

namespace Cekta\DI\Test\Loader;

use Cekta\DI\Loader\Alias;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AliasTest extends TestCase
{
    final public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with('a')->willReturn('test');
        assert($container instanceof ContainerInterface);

        $this->assertEquals('test', (new Alias('a'))($container));
    }
}
