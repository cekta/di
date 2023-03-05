<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\InfiniteRecursionDetector;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class InfiniteRecursionDetectorTest extends TestCase
{
    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetMustPopCall(): void
    {
        $mock = $this->createMock(ContainerInterface::class);
        $mock->method('get')
            ->willReturn(123);
        assert($mock instanceof ContainerInterface);
        $container = new InfiniteRecursionDetector($mock);
        $this->assertSame(123, $container->get('a'));
        $this->assertSame(123, $container->get('a'));
    }
}
