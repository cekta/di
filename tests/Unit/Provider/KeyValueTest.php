<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\LoaderInterface;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\ProviderException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class KeyValueTest extends TestCase
{
    /** @var MockObject */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testCanProvide(): void
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
        $provider = new KeyValue(['key' => 'value']);
        assert($this->container instanceof ContainerInterface);
        static::assertEquals('value', $provider->provide('key'));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideNotFound(): void
    {
        $this->expectException(NotFound::class);
        assert($this->container instanceof ContainerInterface);
        (new KeyValue([]))->provide('magic');
    }

    /**
     * @throws ProviderException
     */
    public function testProvideLoader(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->method('__invoke')->willReturn('test');
        $provider = new KeyValue(['key' => $loader]);
        $result = $provider->provide('key');
        $this->assertInstanceOf(LoaderInterface::class, $result);
        assert($this->container instanceof ContainerInterface);
        $this->assertEquals('test', $result($this->container));
    }

    /**
     * @throws ProviderException
     */
    public function testTransform(): void
    {
        $provider = KeyValue::transform([
            'a' => function () {
                return new stdClass();
            },
            'b' => 123
        ]);
        assert($this->container instanceof ContainerInterface);
        $this->assertSame(123, $provider->provide('b'));
        $this->assertInstanceOf(stdClass::class, $provider->provide('a')($this->container));
    }
}
