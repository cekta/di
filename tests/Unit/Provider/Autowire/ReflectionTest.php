<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire;

use Cekta\DI\Provider\Autowire\Reflection;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

class ReflectionTest extends TestCase
{
    public function testReadDependencies()
    {
        $class = new Reflection(stdClass::class);
        self::assertSame([], $class->getDependencies());
    }

    /**
     * @throws ReflectionException
     */
    public function testReadDependenciesWithArguments()
    {
        $obj = new class(new stdClass(), 1)
        {
            public $a;
            public $b;

            public function __construct(stdClass $a, int $b)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $name = get_class($obj);
        $class = new Reflection($name);
        self::assertSame([stdClass::class, 'b'], $class->getDependencies());
    }
}
