<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\LoaderInterface;
use Cekta\DI\Provider\KeyValue;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class KeyValueTest extends TestCase
{
    /** @var ContainerInterface | null - Mock Container */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testHasProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        $this->assertTrue($provider->hasProvide('key'));
        $this->assertFalse($provider->hasProvide('invalid name'));
    }

    public function testProvide(): void
    {
        $this->assertEquals(
            'value',
            (new KeyValue(['key' => 'value']))->provide('key', $this->container)
        );
    }

    public function testProvideNotFound(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        (new KeyValue([]))->provide('magic', $this->container);
    }

    public function testProvideLoader(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('__invoke')->willReturn('test');
        $provider = new KeyValue(['key' => $loader]);

        $this->assertEquals(
            'test',
            $provider->provide('key', $this->container)
        );
    }
}
