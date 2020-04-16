<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Loader\Factory;
use Cekta\DI\Loader\FactoryVariadic;
use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\ProviderException;
use Cekta\DI\Provider;
use Cekta\DI\Reflection;
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
        $this->assertInstanceOf(Provider::class, $this->provider);
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
     * @throws ProviderException
     */
    public function testProvide(): void
    {
        $this->reflection->method('isInstantiable')->willReturn(true);
        $this->reflection->method('getDependencies')->with('test')->willReturn([]);
        $this->assertInstanceOf(Factory::class, $this->provider->provide('test'));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideNotFound(): void
    {
        $this->expectException(NotFound::class);
        $this->provider->provide('magic');
    }

    /**
     * @throws ProviderException
     */
    public function testProvideVariadic(): void
    {
        $this->reflection->method('isInstantiable')->willReturn(true);
        $this->reflection->method('isVariadic')->with('test')->willReturn(true);
        $this->assertInstanceOf(FactoryVariadic::class, $this->provider->provide('test'));
    }
}
