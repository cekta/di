<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\ProviderNotFound;
use Cekta\DI\LoaderInterface;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class ContainerTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $provider;
    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ProviderInterface::class);
        assert($this->provider instanceof ProviderInterface);
        $this->container = new Container($this->provider);
    }

    public function testGetInvalidName(): void
    {
        $this->expectException(ProviderNotFound::class);
        $this->container->get('invalid name');
    }

    public function testGet(): void
    {
        $this->provider->expects(static::once())->method('provide')->willReturn('test');
        $this->provider->expects(static::once())->method('canProvide')->willReturn(true);
        static::assertEquals('test', $this->container->get('name'));
    }

    public function testHas(): void
    {
        $this->provider->expects(static::never())->method('provide');
        $this->provider->expects(static::exactly(2))->method('canProvide')
            ->willReturnCallback(static function ($name) {
                return $name === 'magic';
            });
        static::assertTrue($this->container->has('magic'));
        static::assertFalse($this->container->has('invalid name'));
    }

    public function testInfiniteRecursion(): void
    {
        $this->expectException(InfiniteRecursion::class);
        $fooA = $this->createMock(LoaderInterface::class);
        $fooA->expects($this->once())->method('__invoke')->willReturnCallback(function (ContainerInterface $c) {
            return $c->get('FooB');
        });
        $fooB = $this->createMock(LoaderInterface::class);
        $fooB->expects($this->once())->method('__invoke')->willReturnCallback(function (ContainerInterface $c) {
            return $c->get('FooA');
        });
        $this->provider->method('canProvide')->willReturn(true);
        $this->provider->expects($this->exactly(2))->method('provide')
            ->willReturnMap([
                ['FooA', $fooA],
                ['FooB', $fooB]
            ]);
        $this->container->get('FooA');
    }

    public function testGetFindProviderOnce(): void
    {
        $this->provider->expects($this->once())->method('provide')
            ->with('a')
            ->willReturn(new stdClass());
        $this->provider->expects($this->once())->method('canProvide')
            ->with('a')
            ->willReturn(true);
        $result = $this->container->get('a');
        $result2 = $this->container->get('a');
        static::assertEquals(new stdClass(), $result);
        static::assertSame($result, $result2);
    }

    public function testGetExceptionInProvider(): void
    {
        $this->expectException(ProviderExceptionInterface::class);
        $this->provider->expects($this->once())->method('canProvide')
            ->with('a')
            ->willReturn(true);
        $exception = new class extends Exception implements ProviderExceptionInterface
        {
        };
        $this->provider->expects($this->once())->method('provide')
            ->with('a')
            ->willThrowException($exception);
        $this->container->get('a');
    }

    public function testGetClosure(): void
    {
        $closure = function (ContainerInterface $container) {
            $this->assertNotEmpty($container);
            return 123;
        };
        $this->provider->method('canProvide')->willReturn(true);
        $this->provider->method('provide')->willReturn($closure);
        $this->assertSame(123, $this->container->get('test'));
    }

    public function testGetLoader(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('__invoke')->willReturn('123');
        $this->provider->method('canProvide')->willReturn(true);
        $this->provider->method('provide')->with('test')->willReturn($loader);
        $this->assertSame('123', $this->container->get('test'));
    }
}
