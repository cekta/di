<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Lazy;

use Cekta\DI\Lazy\Container;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerTest extends TestCase
{
    public function testLoad(): void
    {
        $mock = $this->createMock(ContainerInterface::class);
        $container = new Container();
        Assert::assertSame($mock, $container->load($mock));
    }
}
