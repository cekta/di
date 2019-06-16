<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\LoaderInterface;
use Cekta\DI\Provider\KeyValue;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class KeyValueTest extends TestCase
{
    /** @var ContainerInterface|null|MockObject - Mock Container */
    private $container;

    /** @throws Exception */
    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testHasProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertTrue($provider->hasProvide('key'));
        static::assertFalse($provider->hasProvide('invalid name'));
    }

    public function testProvide(): void
    {
        /** needs hard type correction */
        assert($this->container instanceof ContainerInterface);
        static::assertEquals('value', (new KeyValue(['key' => 'value']))
            ->provide('key', $this->container));
    }

    public function testProvideNotFound(): void
    {
        assert($this->container instanceof ContainerInterface);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        (new KeyValue([]))->provide('magic', $this->container);
    }

    public function testProvideLoader(): void
    {
        assert($this->container instanceof ContainerInterface);
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('__invoke')->willReturn('test');
        $provider = new KeyValue(['key' => $loader]);

        static::assertEquals('test', $provider->provide('key', $this->container));
    }
}
