<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\InfiniteRecursionDetector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class InfiniteRecursionDetectorTest extends TestCase
{
    /**
     * @var InfiniteRecursionDetector
     */
    private $container;
    /**
     * @var MockObject
     */
    private $mock;

    protected function setUp(): void
    {
        $this->mock = $this->createMock(ContainerInterface::class);
        $this->container = new InfiniteRecursionDetector($this->mock);
    }

    public function testHas(): void
    {
        $this->mock->method('has')->willReturnMap([['invalid', false], ['valid', true]]);
        $this->assertFalse($this->container->has('invalid'));
        $this->assertTrue($this->container->has('valid'));
    }

    public function testGet(): void
    {
        $this->mock->method('get')->willReturn(123);
        $this->assertSame(123, $this->container->get('test'));
        $this->assertSame(123, $this->container->get('test'));
    }

    public function testInfiniteRecursion(): void
    {
        $this->expectException(InfiniteRecursion::class);
        $container = $this->container;
        $this->mock->method('get')->willReturnCallback(function () use ($container) {
            $container->get('a');
        });
        $this->container->get('a');
    }
}
