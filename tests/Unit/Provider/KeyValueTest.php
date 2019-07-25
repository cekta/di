<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\KeyValue\LoaderInterface;
use Cekta\DI\ProviderException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/** @covers \Cekta\DI\Provider\KeyValue */
class KeyValueTest extends TestCase
{
    /** @var ContainerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testCanProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertTrue($provider->canBeProvided('key'));
        static::assertFalse($provider->canBeProvided('invalid name'));
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
        $this->expectException(NotFound::class);
        assert($this->container instanceof ContainerInterface);
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
