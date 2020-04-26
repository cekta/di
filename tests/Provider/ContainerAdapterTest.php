<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\ContainerAdapter;
use Cekta\DI\ProviderException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerAdapterTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $container;
    /**
     * @var ContainerAdapter
     */
    private $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->createMock(ContainerInterface::class);
        assert($this->container instanceof ContainerInterface);
        $this->provider = new ContainerAdapter($this->container);
    }

    public function testCanProvide(): void
    {
        $this->container->method('has')->willReturnMap(
            [
                ['test', true],
                ['bad', false]
            ]
        );
        $this->assertTrue($this->provider->canProvide('test'));
        $this->assertFalse($this->provider->canProvide('bad'));
    }

    /**
     * @throws ProviderException
     */
    public function testProvide(): void
    {
        $result = 'result';
        $id = 'test';
        $this->container->method('get')->with($id)->willReturn($result);
        $this->assertSame($result, $this->provider->provide($id));
    }
}
