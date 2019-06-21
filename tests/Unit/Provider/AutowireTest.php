<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowire;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Throwable;

/**
 * @covers \Cekta\DI\Provider\Autowire
 */
class AutowireTest extends TestCase
{
    /** @var MockObject|ContainerInterface|null */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testHasProvide(): void
    {
        $provider = new Autowire();
        static::assertTrue($provider->hasProvide(stdClass::class));
        static::assertFalse($provider->hasProvide('invalid name'));
        static::assertFalse($provider->hasProvide(Throwable::class));
    }

    public function testProvideWithoutArguments(): void
    {
        assert($this->container instanceof ContainerInterface);
        static::assertEquals(new stdClass(), (new Autowire())
            ->provide(stdClass::class, $this->container));
    }

    public function testProvideInvalidName(): void
    {
        assert($this->container instanceof ContainerInterface);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        (new Autowire())->provide('magic', $this->container);
    }
}
