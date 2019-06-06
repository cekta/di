<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit;

use Cekta\DI\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @covers \Cekta\DI\Container
 */
class ContainerTest extends TestCase
{
    public function testGetInvalidName()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `invalid name` not found');

        $container = new Container([]);
        $container->get('invalid name');
    }
}
