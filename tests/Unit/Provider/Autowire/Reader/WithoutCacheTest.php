<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire\Reader;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;
use Cekta\DI\Provider\Autowire\Reader\WithoutCache;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class WithoutCacheTest extends TestCase
{
    public function testGetDependencies()
    {
        $obj = new class () {
        };
        $name = get_class($obj);
        $reader = new WithoutCache();
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

    public function testGetDependenciesInvalidName()
    {
        $this->expectException(InvalidClassName::class);
        $this->expectExceptionMessage('Invalid class name: `invalid name`');
        $reader = new WithoutCache();
        $reader->getDependencies('invalid name');
    }

    public function testMustBeReader()
    {
        $reader = new WithoutCache();
        $this->assertInstanceOf(ReaderInterface::class, $reader);
    }
}
