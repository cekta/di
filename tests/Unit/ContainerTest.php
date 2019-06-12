<?php

namespace Cekta\DI\Test\Unit;

use Psr\Container\NotFoundExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Cekta\DI\Container;

/** @covers \Cekta\DI\Container */
class ContainerTest extends TestCase
{
    final public function testGetInvalidName(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `invalid name` not found');

        $container = new Container(...[]);
        $container->get('invalid name');
    }

    final public function testGet(): void
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->once())->method('provide')->willReturn('test');
        $provider->expects($this->once())->method('hasProvide')->willReturn(true);

        $this->assertEquals('test', (new Container($provider))->get('name'));
    }

    final public function testHas(): void
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->never())->method('provide');
        $provider->expects($this->exactly(2))->method('hasProvide')
            ->willReturnCallback(static function ($name) {
                return $name === 'magic';
            });

        $this->assertTrue((new Container($provider))->has('magic'));
        $this->assertFalse((new Container($provider))->has('invalid name'));
    }
}
