<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Reflection;
use Cekta\DI\ProviderInterface;
use Cekta\DI\ReflectionTransformerInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class ReflectionTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        $this->service = new Reflection();
    }

    public function testGetDependenciesWithoutConstructor()
    {
        $this->assertSame([], $this->service->getDependencies(stdClass::class));
    }

    public function testGetDependenciesWithContructorArguments()
    {
        $obj = new class (new stdClass()) {
            public $class;
            public $a;
            public $b;
            public function __construct(stdClass $class, int $a = 1, $b = 2)
            {
                $this->class = $class;
                $this->a = $a;
                $this->b = $b;
            }
        };
        $name = get_class($obj);
        $this->assertSame([stdClass::class, 'a', 'b'], $this->service->getDependencies($name));
    }

    public function testGetDependenciesWithVariadic()
    {
        $obj = new class (new stdClass()) {
            public $args;
            public function __construct(stdClass ...$args)
            {
                $this->args = $args;
            }
        };
        $this->assertSame(['args'], $this->service->getDependencies(get_class($obj)));
    }

    public function testGetDependenciesWithTransform()
    {
        $obj = new class () {
            private $a;
            private $b;

            public function __construct($a = 1, $b = 2)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $name = get_class($obj);
        $transform = $this->createMock(ReflectionTransformerInterface::class);
        $transform->expects($this->once())->method('transform')->with($name, ['a', 'b'])->willReturn(['c', 'b']);
        assert($transform instanceof ReflectionTransformerInterface);
        $reflection = new Reflection($transform);
        $this->assertSame(['c', 'b'], $reflection->getDependencies($name));
    }

    public function testIsVariadic()
    {
        $obj = new class () {
            public $args;
            public function __construct(...$args)
            {
                $this->args = $args;
            }
        };
        $this->assertTrue($this->service->isVariadic(get_class($obj)));
        $this->assertFalse($this->service->isVariadic(stdClass::class));
    }

    public function testIsInstantiable()
    {
        $this->assertTrue($this->service->isInstantiable(stdClass::class));
        $this->assertFalse($this->service->isInstantiable(TestCase::class));
        $this->assertFalse($this->service->isInstantiable(ProviderInterface::class));
    }

    public function testInvalideClass()
    {
        $this->assertFalse($this->service->isInstantiable('invalide name'));
        $this->assertSame([], $this->service->getDependencies('invalide name'));
        $this->assertFalse($this->service->isVariadic('invalide name'));
    }
}
