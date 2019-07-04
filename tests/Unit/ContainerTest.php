<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Cekta\DI\Container;
use Cekta\DI\Exception\IdNotString;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\NotProvideble;
use Cekta\DI\Exception\ProviderNotFound;
use Cekta\DI\ProviderException;
use Cekta\DI\ProviderInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use stdClass;

/** @covers \Cekta\DI\Container */
class ContainerTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testGetInvalidName(): void
    {
        $this->expectException(ProviderNotFound::class);

        $container = new Container(...[]);
        $container->get('invalid name');
    }

    /**
     * @throws ContainerExceptionInterface
     */
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

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInfiniteRecursion(): void
    {
        $this->expectException(InfiniteRecursion::class);
        $provider = $this->createMock(ProviderInterface::class);
        $provider->method('canProvide')->willReturn(true);
        assert($provider instanceof ProviderInterface);
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

    /**
     * @throws ContainerExceptionInterface
     */
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

    /**
     * @throws ContainerExceptionInterface
     */
    public function testGetNotFoundInProvider()
    {
        $this->expectException(NotProvideble::class);
        $this->expectExceptionMessage('Provider cant load container `a`');
        $provider = $this->createMock(ProviderInterface::class);
        $provider->expects($this->once())->method('canProvide')
            ->with('a')
            ->willReturn(true);
        $provider->expects($this->once())->method('provide')
            ->with('a')
            ->willThrowException(new class extends Exception implements ProviderException
            {
            });
        assert($provider instanceof ProviderInterface);
        $container = new Container($provider);
        $container->get('a');
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testGetNotString()
    {
        $this->expectException(IdNotString::class);
        $container = new Container();
        $container->get(123);
    }
}
