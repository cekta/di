<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Cekta\DI\Container;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\ProviderNotFound;
use Cekta\DI\LoaderInterface;
use Cekta\DI\ProviderException;
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
        $container = new Container(...[]);
        $container->get('invalid name');
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

    public function testGetFindProviderOnce()
    {
        $this->provider->expects($this->once())->method('provide')
            ->with('a')
            ->willReturn(new stdClass());
        $this->provider->expects($this->once())->method('canProvide')
            ->with('a')
            ->willReturn(true);
        $a1 = $this->container->get('a');
        $a2 = $this->container->get('a');
        static::assertEquals(new stdClass(), $a1);
        static::assertSame($a1, $a2);
    }

    public function testGetNotFoundInProvider()
    {
        $this->expectException(ProviderException::class);
        $this->provider->expects($this->once())->method('canProvide')
            ->with('a')
            ->willReturn(true);
        $this->provider->expects($this->once())->method('provide')
            ->with('a')
            ->willThrowException(new class extends Exception implements ProviderException
            {
            });
        $this->container->get('a');
    }
}
