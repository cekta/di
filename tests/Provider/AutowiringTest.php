<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\Autowiring\Reflection;
use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Throwable;

class AutowiringTest extends TestCase
{
    /**
     * @var Autowiring
     */
    private $provider;
    /**
     * @var MockObject
     */
    private $container;
    /**
     * @var MockObject
     */
    private $reflection;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->reflection = $this->createMock(Reflection::class);
        assert($this->reflection instanceof Reflection);
        $this->provider = new Autowiring($this->reflection);
    }

    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    public function testCanProvide(): void
    {
        $this->assertTrue($this->provider->canProvide(stdClass::class));
    }

    public function testCanProvideInvalidName(): void
    {
        $this->assertFalse($this->provider->canProvide('invalid name'));
    }

    public function testCanProvideInterface(): void
    {
        $this->assertFalse($this->provider->canProvide(Throwable::class));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvide(): void
    {
        assert($this->container instanceof ContainerInterface);
        $this->assertEquals(new stdClass(), $this->provider->provide(stdClass::class)($this->container));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideInterface(): void
    {
        $this->expectException(NotFound::class);
        $this->provider->provide(Throwable::class);
    }
}
