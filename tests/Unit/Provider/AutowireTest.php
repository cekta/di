<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Autowire;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use stdClass;
use Throwable;

class AutowireTest extends TestCase
{
    public function testHasProvide()
    {
        $provider = new Autowire();
        $this->assertTrue($provider->hasProvide(stdClass::class));
        $this->assertFalse($provider->hasProvide('invalid name'));
        $this->assertFalse($provider->hasProvide(Throwable::class));
    }

    /**
     * @throws ReflectionException
     */
    public function testProvideWithoutArguments()
    {
        $provider = new Autowire();
        $this->assertEquals(new stdClass(), $provider->provide(stdClass::class, $this->getContainerMock()));
    }

    /**
     * @return ContainerInterface
     * @throws ReflectionException
     */
    private function getContainerMock(): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        /** @var ContainerInterface $container */
        return $container;
    }

    /**
     * @throws ReflectionException
     */
    public function testProvideInvalidName()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        $provider = new Autowire();
        $provider->provide('magic', $this->getContainerMock());
    }
}
