<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\Autowiring\Reflection;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

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
        $class = $this->createMock(Autowiring\ReflectionClass::class);
        $class->method('isInstantiable')->willReturn(true);
        $this->reflection->expects($this->once())
            ->method('getClass')
            ->with('test')
            ->willReturn($class);
        $this->assertTrue($this->provider->canProvide('test'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvide(): void
    {
        $class = $this->createMock(Autowiring\ReflectionClass::class);
        $class->method('getDependencies')->willReturn([]);
        $this->reflection->method('getClass')->with(stdClass::class)->willReturn($class);
        assert($this->container instanceof ContainerInterface);
        $this->assertEquals(new stdClass(), $this->provider->provide(stdClass::class)($this->container));
    }
}
