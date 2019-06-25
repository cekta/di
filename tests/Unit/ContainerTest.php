<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Cekta\DI\Container;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

/** @covers \Cekta\DI\Container */
class ContainerTest extends TestCase
{
    public function testGetInvalidName(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `invalid name` not found');

        $container = new Container(...[]);
        $container->get('invalid name');
    }

    public function testGet(): void
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects(static::once())->method('provide')->willReturn('test');
        $provider->expects(static::once())->method('canProvide')->willReturn(true);
        assert($provider instanceof ProviderInterface);

        static::assertEquals('test', (new Container($provider))->get('name'));
    }

    public function testHas(): void
    {
        $provider = $this->createMock(ProviderInterface::class);
        assert($provider instanceof ProviderInterface);
        $provider->expects(static::never())->method('provide');
        $provider->expects(static::exactly(2))->method('canProvide')
            ->willReturnCallback(static function ($name) {
                return $name === 'magic';
            });

        static::assertTrue((new Container($provider))->has('magic'));
        static::assertFalse((new Container($provider))->has('invalid name'));
    }
}
