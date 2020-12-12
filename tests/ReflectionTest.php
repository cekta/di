<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Reflection;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class ReflectionTest extends TestCase
{
    /**
     * @var Reflection
     */
    private $service;

    protected function setUp(): void
    {
        $this->service = new Reflection();
    }

    public function testGetDependenciesWithoutConstructor(): void
    {
        $this->assertSame([], $this->service->getDependencies(stdClass::class));
    }

    public function testGetDependenciesInvalidClass(): void
    {
        $this->assertSame([], $this->service->getDependencies('invalid name'));
    }

    public function testGetDependenciesWithContructorArguments(): void
    {
        $obj = new class (new stdClass()) {
            /**
             * @var stdClass
             */
            public $class;
            /**
             * @var int
             */
            public $a;
            /**
             * @var int
             */
            public $b;

            public function __construct(stdClass $class, int $a = 1, int $b = 2)
            {
                $this->class = $class;
                $this->a = $a;
                $this->b = $b;
            }
        };
        $name = get_class($obj);
        $this->assertSame([stdClass::class, 'a', 'b'], $this->service->getDependencies($name));
    }

    public function testIsInstantiable(): void
    {
        $this->assertTrue($this->service->isInstantiable(stdClass::class));
        $this->assertFalse($this->service->isInstantiable(TestCase::class));
        $this->assertFalse($this->service->isInstantiable(ContainerInterface::class));
        $this->assertFalse($this->service->isInstantiable('invalid name'));
    }

    public function testInjection(): void
    {
        $obj = new class (1, 2) {
            /**
             * @var int
             */
            private $a;
            /**
             * @var int
             */
            private $b;

            /**
             * @param int $a
             * @param int $b
             * @inject a\magic $a
             * @inject b\magic $b
             */
            public function __construct(int $a, int $b)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $class = get_class($obj);
        $this->assertSame(['a\magic', 'b\magic'], $this->service->getDependencies($class));
    }
}
