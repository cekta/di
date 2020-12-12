<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Container;

use Cekta\DI\Container\Implementation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class ImplementationTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testMustBePsrContainer(): void
    {
        assert($this->container instanceof ContainerInterface);
        $this->assertInstanceOf(ContainerInterface::class, new Implementation([], $this->container));
    }

    public function testGetAndHas(): void
    {
        $this->container->method('get')->willReturn(new StdClass());
        assert($this->container instanceof ContainerInterface);
        $provider = new Implementation(
            [
                'a' => stdClass::class,
                'b' => 123
            ],
            $this->container
        );
        $this->assertSame(123, $provider->get('b'));
        $this->assertInstanceOf(stdClass::class, $provider->get('a'));
        $this->assertTrue($provider->has('a'));
        $this->assertTrue($provider->has('b'));
        $this->assertFalse($provider->has('invalide name'));
    }
}
