<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\Strategy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class StrategyTest extends TestCase
{
    /**
     * @var Strategy
     */
    private $container;
    /**
     * @var MockObject
     */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ContainerInterface::class);
        $this->container = new Strategy($this->provider);
    }

    public function testHas(): void
    {
        $this->provider->method('has')->willReturnMap([['valid', true], ['bad', false]]);
        $this->assertTrue($this->container->has('valid'));
        $this->assertFalse($this->container->has('bad'));
    }

    public function testGet(): void
    {
        $this->provider->method('has')->willReturn(true);
        $this->provider->method('get')->willReturn(123);
        $this->assertSame(123, $this->container->get('test'));
    }

    public function testGetNotFound(): void
    {
        $this->expectException(NotFound::class);
        $this->container->get('invalid name');
    }
}
