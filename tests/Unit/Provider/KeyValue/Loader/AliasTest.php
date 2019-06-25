<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\KeyValue\Loader;

use Cekta\DI\Provider\KeyValue\Loader\Alias;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AliasTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $container->method('get')->with('a')->willReturn('test');
        static::assertEquals('test', (new Alias('a'))($container));
    }
}
