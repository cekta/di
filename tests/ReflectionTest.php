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

    public function testGetDependenciesWithConstructorArguments(): void
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
        $this->assertSame(['a\magic', 'b\magic'], $this->service->getDependencies(C::class));
    }

    public function testUnionType(): void
    {
        $obj = new class (1, 2) {
            public int | string | stdClass $param1;
            public int | string $param2;

            public function __construct(int | string | stdClass $param1, int | string $param2)
            {
                $this->param1 = $param1;
                $this->param2 = $param2;
            }
        };
        $class = get_class($obj);
        $this->assertSame([stdClass::class, 'param2'], $this->service->getDependencies($class));
    }

    public function testWithoutType(): void
    {
        $obj = new class (1) {
            /**
             * @phpstan-ignore-next-line
             */
            private $param;

            /**
             * @param $param
             * @phpstan-ignore-next-line
             */
            public function __construct($param)
            {
                $this->param = $param;
            }

            /**
             * @return mixed
             */
            public function getParam()
            {
                return $this->param;
            }
        };
        $class = get_class($obj);
        $this->assertSame(['param'], $this->service->getDependencies($class));
    }
}
