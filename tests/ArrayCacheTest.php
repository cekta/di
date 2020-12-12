<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ArrayCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ArrayCacheTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $mock;
    /**
     * @var ArrayCache
     */
    private $container;

    protected function setUp(): void
    {
        $this->mock = $this->createMock(ContainerInterface::class);
        $this->container = new ArrayCache($this->mock);
    }

    public function testHas(): void
    {
        $this->mock->method('has')->willReturnMap([['invalid', false], ['valid', true]]);
        $this->assertFalse($this->container->has('invalid'));
        $this->assertTrue($this->container->has('valid'));
    }

    public function testGet(): void
    {
        $this->mock->expects($this->once())->method('get')->willReturn(123);
        $this->assertSame(123, $this->container->get('test'));
        $this->assertSame(123, $this->container->get('test'), 'must be use cache');
    }
}
