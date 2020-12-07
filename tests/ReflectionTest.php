<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Reflection;
use Cekta\DI\Provider;
use PHPUnit\Framework\TestCase;
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

    public function testGetDependenciesWithVariadic(): void
    {
        $obj = new class (new stdClass()) {
            /**
             * @var stdClass[]
             */
            public $args;

            public function __construct(stdClass ...$args)
            {
                $this->args = $args;
            }
        };
        $this->assertSame(['args'], $this->service->getDependencies(get_class($obj)));
    }

    public function testIsVariadic(): void
    {
        $obj = new class () {
            /**
             * @var int[]
             */
            public $args;

            public function __construct(int ...$args)
            {
                $this->args = $args;
            }
        };
        $this->assertTrue($this->service->isVariadic(get_class($obj)));
        $this->assertFalse($this->service->isVariadic(stdClass::class));
    }

    public function testIsInstantiable(): void
    {
        $this->assertTrue($this->service->isInstantiable(stdClass::class));
        $this->assertFalse($this->service->isInstantiable(TestCase::class));
        $this->assertFalse($this->service->isInstantiable(Provider::class));
    }

    public function testInvalideClass(): void
    {
        $this->assertFalse($this->service->isInstantiable('invalide name'));
        $this->assertSame([], $this->service->getDependencies('invalide name'));
        $this->assertFalse($this->service->isVariadic('invalide name'));
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

    public function testIsVariadicWithoutParams(): void
    {
        $obj = new class () {
            public function __construct()
            {
            }
        };
        $this->assertSame(false, $this->service->isVariadic(get_class($obj)));
    }
}
