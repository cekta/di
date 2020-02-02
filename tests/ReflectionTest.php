<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Reflection;
use Cekta\DI\ProviderInterface;
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

    public function testGetDepndenciesWithContructorArguments()
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

    public function testIsInstantiable()
    {
        $this->assertTrue($this->service->isInstantiable(stdClass::class));
        $this->assertFalse($this->service->isInstantiable(TestCase::class));
        $this->assertFalse($this->service->isInstantiable(ProviderInterface::class));
    }

    public function testGetClass()
    {
        $this->assertFalse($this->service->isInstantiable('invalide name'));
        $this->assertSame([], $this->service->getDependencies('invalide name'));
    }
}
