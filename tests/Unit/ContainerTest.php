<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Cekta\DI\Container;
use Cekta\DI\Exception\NotFoundInProvider;
use Cekta\DI\ProviderInterface;
use Cekta\DI\ProviderNotFoundException;
use Exception;
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

        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('canProvide')->willReturn(true);
        $container = new Container($provider);
        $provider->expects($this->exactly(2))->method('provide')
            ->willReturnCallback(function (string $id, ContainerInterface $container) {
                $transform = [
                    'FooA' => 'FooB',
                    'FooB' => 'FooA'
                ];
                return $container->get($transform[$id]);
            });
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

    public function testGetNotFoundInProvider()
    {
        $this->expectException(NotFoundInProvider::class);
        $this->expectExceptionMessage('Provider cant load container `a`');
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->once())->method('canProvide')
            ->with('a')
            ->willReturn(true);
        $provider->expects($this->once())->method('provide')
            ->with('a')
            ->willThrowException(new class extends Exception implements ProviderNotFoundException
            {
            });
        assert($provider instanceof ProviderInterface);
        $container = new Container($provider);
        $container->get('a');
    }
}
