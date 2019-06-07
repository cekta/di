<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\LoaderInterface;
use Cekta\DI\Provider\KeyValue;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class KeyValueTest extends TestCase
{
    public function testHasProvide()
    {
        $provider = new KeyValue(['a' => 'value']);
        $this->assertTrue($provider->hasProvide('a'));
        $this->assertFalse($provider->hasProvide('invalid name'));
    }

    /**
     * @throws ReflectionException
     */
    public function testProvide()
    {
        $provider = new KeyValue(['a' => 'value']);
        $this->assertEquals('value', $provider->provide('a', $this->getContainerMock()));
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
    public function testProvideNotFound()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        $provider = new KeyValue([]);
        $provider->provide('magic', $this->getContainerMock());
    }

    /**
     * @throws ReflectionException
     */
    public function testProvideLoader()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('__invoke')
            ->willReturn(123);
        $provider = new KeyValue([
            'a' => $loader
        ]);
        $this->assertEquals(123, $provider->provide('a', $this->getContainerMock()));
    }
}
