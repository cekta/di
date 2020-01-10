<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider\Autowiring;

use Cekta\DI\Provider\Autowiring\Reflection;
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
        $this->assertSame([], $this->service->getClass(stdClass::class)->getDependencies());
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
        $this->assertSame([stdClass::class, 'a', 'b'], $this->service->getClass($name)->getDependencies());
    }

    public function testIsInstantiable()
    {
        $this->assertTrue($this->service->getClass(stdClass::class)->isInstantiable());
        $this->assertFalse($this->service->getClass(TestCase::class)->isInstantiable());
        $this->assertFalse($this->service->getClass(ProviderInterface::class)->isInstantiable());
    }

    public function testGetClass()
    {
        $nullResult = $this->service->getClass('invalide name');
        $this->assertFalse($nullResult->isInstantiable());
        $this->assertSame([], $nullResult->getDependencies());
    }
}
