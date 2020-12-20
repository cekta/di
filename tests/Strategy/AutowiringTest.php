<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Strategy;

use Cekta\DI\Strategy\Autowiring;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Reflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

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
    /**
     * @var MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $this->reflection = $this->createMock(Reflection::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->provider = new Autowiring($this->reflection, $this->container);
    }

    public function testMustBePsrContainer(): void
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->provider);
    }

    public function testHas(): void
    {
        $this->reflection
            ->method('isInstantiable')
            ->willReturnMap([['test', true], ['invalid', false]]);
        $this->assertTrue($this->provider->has('test'));
        $this->assertFalse($this->provider->has('invalid'));
    }

    public function testGet(): void
    {
        $this->reflection->method('isInstantiable')->willReturn(true);
        $this->reflection->method('getDependencies')->willReturn([]);
        $this->assertInstanceOf(stdClass::class, $this->provider->get(stdClass::class));
    }

    public function testGetWithDependency(): void
    {
        $obj = new class (123, 123) {
            /**
             * @var int
             */
            public $a;
            /**
             * @var int
             */
            public $b;

            public function __construct(int $a, int $b)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $this->reflection->method('isInstantiable')->willReturn(true);
        $this->reflection->method('getDependencies')->willReturn(['a', 'b']);
        $this->container->method('get')->willReturnMap(
            [
                ['a', 1],
                ['b', 2]
            ]
        );
        $class = get_class($obj);
        $result = $this->provider->get($class);
        $this->assertSame(1, $result->a);
        $this->assertSame(2, $result->b);
    }

    public function testGetNotFound(): void
    {
        $this->expectException(NotFound::class);
        $this->provider->get('magic');
    }
}
