<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Cekta\DI\Container;

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

    /** @throws Exception */
    public function testGet(): void
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects(static::once())->method('provide')->willReturn('test');
        $provider->expects(static::once())->method('hasProvide')->willReturn(true);
        assert($provider instanceof ProviderInterface);

        static::assertEquals('test', (new Container($provider))->get('name'));
    }

    /** @throws Exception */
    public function testHas(): void
    {
        $provider = $this->createMock(ProviderInterface::class);
        assert($provider instanceof ProviderInterface);
        $provider->expects(static::never())->method('provide');
        $provider->expects(static::exactly(2))->method('hasProvide')
            ->willReturnCallback(static function ($name) {
                return $name === 'magic';
            });

        static::assertTrue((new Container($provider))->has('magic'));
        static::assertFalse((new Container($provider))->has('invalid name'));
    }
}
