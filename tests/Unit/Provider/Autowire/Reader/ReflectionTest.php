<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire\Reader;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;
use Cekta\DI\Provider\Autowire\Reader\Reflection;
use Cekta\DI\Provider\Autowire\ReaderException;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Cekta\DI\Provider\Autowire\Reader\RuleInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class ReflectionTest extends TestCase
{
    /**
     * @throws ReaderException
     */
    public function testGetDependencies()
    {
        $obj = new class () {
        };
        $name = get_class($obj);
        $reader = new Reflection();
        $this->assertSame([], $reader->getDependencies($name));

        $obj = new class(new stdClass(), '123')
        {
            /**
             * @var stdClass
             */
            public $class;
            /**
             * @var string
             */
            public $str;

            public function __construct(stdClass $class, string $str)
            {
                $this->class = $class;
                $this->str = $str;
            }
        };
        $name = get_class($obj);
        $this->assertSame([stdClass::class, 'str'], $reader->getDependencies($name));
    }

    /**
     * @throws ReaderException
     */
    public function testGetDependenciesInvalidName()
    {
        $this->expectException(InvalidClassName::class);
        $this->expectExceptionMessage('Invalid class name: `invalid name`');
        $reader = new Reflection();
        $reader->getDependencies('invalid name');
    }

    public function testMustImplementReaderInterface()
    {
        $reader = new Reflection();
        $this->assertInstanceOf(ReaderInterface::class, $reader);
    }

    /**
     * @throws ReaderException
     */
    public function testGetDependenciesWithRule()
    {
        $obj = new class(new stdClass(), '123')
        {
            /**
             * @var stdClass
             */
            public $class;
            /**
             * @var string
             */
            public $str;

            public function __construct(stdClass $class, string $str)
            {
                $this->class = $class;
                $this->str = $str;
            }
        };
        $name = get_class($obj);
        $rule = $this->createMock(RuleInterface::class);
        $rule->method('acceptable')->willReturn(true);
        $rule->method('accept')->willReturn(['str' => 'magic']);
        $rule2 = $this->createMock(RuleInterface::class);
        $rule2->method('acceptable')->willReturn(true);
        $rule2->method('accept')->willReturn(['str' => 'must not use']);
        assert($rule instanceof RuleInterface);
        assert($rule2 instanceof RuleInterface);
        $reader = new Reflection($rule, $rule2);
        $this->assertSame([stdClass::class, 'magic'], $reader->getDependencies($name));
    }
}
