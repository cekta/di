<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowire;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;
use stdClass;

class AutowireTest extends TestCase
{
    final public function testHasProvide(): void
    {
        $provider = new Autowire();
        $this->assertTrue($provider->hasProvide(stdClass::class));
        $this->assertFalse($provider->hasProvide('invalid name'));
        $this->assertFalse($provider->hasProvide(Throwable::class));
    }

    final public function testProvideWithoutArguments(): void
    {
        $this->assertEquals(
            new stdClass(),
            (new Autowire())->provide(
                stdClass::class,
                $this->getContainerMock()
            )
        );
    }

    /** @return ContainerInterface */
    private function getContainerMock(): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        return $container;
    }

    final public function testProvideInvalidName(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        (new Autowire())->provide('magic', $this->getContainerMock());
    }
}
