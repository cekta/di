<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\Provider\KeyValue\LoaderInterface;
use Cekta\DI\ProviderException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/** @covers \Cekta\DI\Provider\KeyValue */
class KeyValueTest extends TestCase
{
    /** @var ContainerInterface|null|MockObject - Mock Container */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testHasProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertTrue($provider->canProvide('key'));
        static::assertFalse($provider->canProvide('invalid name'));
    }

    /**
     * @throws ProviderException
     */
    public function testProvide(): void
    {
        /** needs hard type correction */
        assert($this->container instanceof ContainerInterface);
        static::assertEquals('value', (new KeyValue(['key' => 'value']))
            ->provide('key', $this->container));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideNotFound(): void
    {
        $this->expectException(NotFound::class);
        assert($this->container instanceof ContainerInterface);
        (new KeyValue([]))->provide('magic', $this->container);
    }

    /**
     * @throws ProviderException
     */
    public function testProvideLoader(): void
    {
        assert($this->container instanceof ContainerInterface);
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('__invoke')->willReturn('test');
        $provider = new KeyValue(['key' => $loader]);

        static::assertEquals('test', $provider->provide('key', $this->container));
    }

    /**
     * @throws ProviderNotFoundException
     */
    public function testTransform(): void
    {
        $result = KeyValue::transform([
            'a' => function () {
                return new stdClass();
            },
            'b' => 123
        ]);
        $contaienr = $this->createMock(ContainerInterface::class);
        assert($contaienr instanceof ContainerInterface);
        $this->assertSame(123, $result->provide('b', $contaienr));
        $this->assertInstanceOf(stdClass::class, $result->provide('a', $contaienr));
    }
}
