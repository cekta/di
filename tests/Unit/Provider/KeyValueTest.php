<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\LoaderInterface;
use Cekta\DI\Provider\KeyValue;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class KeyValueTest extends TestCase
{
    final public function testHasProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        $this->assertTrue($provider->hasProvide('key'));
        $this->assertFalse($provider->hasProvide('invalid name'));
    }

    final public function testProvide(): void
    {
        $this->assertEquals(
            'value',
            (new KeyValue(['key' => 'value']))->provide(
                'key',
                $this->getContainerMock()
            )
        );
    }

    final public function testProvideNotFound(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        (new KeyValue([]))->provide('magic', $this->getContainerMock());
    }

    final public function testProvideLoader(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('__invoke')->willReturn('test');
        $provider = new KeyValue([ 'a' => $loader ]);

        $this->assertEquals(
            'test',
            $provider->provide('a', $this->getContainerMock())
        );
    }

    /**
     * @return ContainerInterface
     */
    private function getContainerMock(): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        return $container;
    }
}
