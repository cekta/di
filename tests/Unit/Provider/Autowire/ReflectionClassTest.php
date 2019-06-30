<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire;

use Cekta\DI\Provider\Autowire\ReflectionClass;
use Cekta\DI\Provider\Autowire\RuleInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

class ReflectionClassTest extends TestCase
{
    public function testGetDependencies()
    {
        $class = new ReflectionClass(stdClass::class);
        self::assertSame([], $class->getDependencies());
    }

    /**
     * @throws ReflectionException
     */
    public function testGetDependenciesWithArguments()
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
        $class = new ReflectionClass($name);
        self::assertSame([stdClass::class, 'b'], $class->getDependencies());
    }

    /**
     * @throws ReflectionException
     */
    public function testGetDependenciesWithRule()
    {
        $obj = new class('123', '345', '123')
        {
            /**
             * @var string
             */
            public $path;
            /**
             * @var string
             */
            public $another;
            /**
             * @var string
             */
            public $old;

            public function __construct(string $path, string $another, string $old)
            {
                $this->path = $path;
                $this->another = $another;
                $this->old = $old;
            }
        };
        $name = get_class($obj);
        $rule = $this->createMock(RuleInterface::class);
        $rule->expects($this->once())->method('acceptable')
            ->with($name)
            ->willReturn(true);
        $rule->expects($this->once())->method('accept')
            ->willReturn(['path' => 'magic.path']);
        $rule2 = $this->createMock(RuleInterface::class);
        $rule2->expects($this->once())->method('acceptable')
            ->with($name)
            ->willReturn(true);
        $rule2->expects($this->once())->method('accept')
            ->willReturn(['another' => 'magic']);
        assert($rule instanceof RuleInterface);
        assert($rule2 instanceof RuleInterface);
        $class = new ReflectionClass($name, $rule, $rule2);
        static::assertSame(['magic.path', 'magic', 'old'], $class->getDependencies());
    }
}
