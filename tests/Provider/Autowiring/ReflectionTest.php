<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider\Autowiring;

use Cekta\DI\Provider\Autowiring\Reflection;
use PHPUnit\Framework\TestCase;
use ReflectionException;
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

    /**
     * @throws ReflectionException
     */
    public function testGetDependenciesWithoutConstructor()
    {
        $this->assertSame([], $this->service->getDependencies(stdClass::class));
    }

    /**
     * @throws ReflectionException
     */
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
}
