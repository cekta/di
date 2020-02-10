<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Loader\Factory;
use Cekta\DI\Loader\FactoryVariadic;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Reflection;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AutowiringTest extends TestCase
{
    /**
     * @var Autowiring
     */
    private $provider;
    /**
     * @var MockObject
     */
    private $reflection;

    protected function setUp(): void
    {
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
        $this->reflection->expects($this->once())
            ->method('isInstantiable')
            ->with('test')
            ->willReturn(true);
        $this->assertTrue($this->provider->canProvide('test'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvide(): void
    {
        $this->reflection->method('getDependencies')->with('test')->willReturn([]);
        $this->assertInstanceOf(Factory::class, $this->provider->provide('test'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideVariadic(): void
    {
        $this->reflection->method('isVariadic')->with('test')->willReturn(true);
        $this->assertInstanceOf(FactoryVariadic::class, $this->provider->provide('test'));
    }
}
