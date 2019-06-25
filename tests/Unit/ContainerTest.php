<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Cekta\DI\Container;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

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

    public function testInfiniteRecursion(): void
    {
        $this->expectException(ContainerExceptionInterface::class);
        $this->expectExceptionMessage('Infinite recursion for `FooA`, calls: `FooA, FooB`');

        $provider = new class implements ProviderInterface
        {
            public function provide(string $id, ContainerInterface $container)
            {
                if ($id === 'FooA') {
                    return $container->get('FooB');
                }
                return $container->get('FooA');
            }

            public function canProvide(string $id): bool
            {
                return true;
            }
        };
        $container = new Container($provider);
        $container->get('FooA');
    }

    public function testGetFindProviderOnce()
    {
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->once())->method('provide')
            ->with('a')
            ->willReturn(new stdClass());
        $provider->expects($this->once())->method('canProvide')
            ->with('a')
            ->willReturn(true);
        assert($provider instanceof ProviderInterface);
        $container = new Container($provider);
        $a1 = $container->get('a');
        $a2 = $container->get('a');
        static::assertEquals(new stdClass(), $a1);
        static::assertSame($a1, $a2);
    }
}
