<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderException;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Throwable;

/**
 * @covers \Cekta\DI\Provider\Autowiring
 */
class AutowiringTest extends TestCase
{
    public function testMustBeProvider()
    {
        $this->assertInstanceOf(ProviderInterface::class, new Autowiring());
    }

    public function testCanProvide(): void
    {
        $provider = new Autowiring();
        $this->assertTrue($provider->canProvide(stdClass::class));
    }

    public function testCanProvideInvalidName(): void
    {
        $provider = new Autowiring();
        $this->assertFalse($provider->canProvide('invalid name'));
    }

    public function testCanProvideInterface(): void
    {
        $provider = new Autowiring();
        $this->assertFalse($provider->canProvide(Throwable::class));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideWithoutArguments(): void
    {
        $provide = new Autowiring();
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $this->assertEquals(new stdClass(), $provide->provide(stdClass::class, $container));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideWithArguments(): void
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
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->will($this->returnValueMap([
                [stdClass::class, new stdClass()],
                ['str', 'magic']
            ]));
        assert($container instanceof ContainerInterface);
        $provider = new Autowiring();
        $result = $provider->provide($name, $container);
        $this->assertInstanceOf($name, $result);
        $this->assertSame('magic', $result->str);
    }

    /**
     * @throws ProviderException
     */
    public function testProvideReflectionClassNotCreatebale()
    {
        $this->expectException(ClassNotCreated::class);
        $provider = new Autowiring();
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $provider->provide('invalid name', $container);
    }

    /**
     * @throws ProviderException
     */
    public function testProvideWithRules()
    {
        $obj = new class()
        {
            /**
             * @var int
             */
            public $a;
            /**
             * @var int
             */
            public $b;

            public function __construct(int $a = 1, int $b = 2)
            {
                $this->a = $a;
                $this->b = $b;
            }
        };
        $name = get_class($obj);
        $rule = $this->createMock(Autowiring\RuleInterface::class);
        $rule->method('acceptable')->with($name)->willReturn(true);
        $rule->method('accept')->willReturn(['a' => 'c']);
        $provider = new Autowiring($rule);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap([
            ['c', 5],
            ['b', 6]
        ]);
        $result = $provider->provide($name, $container);
        $this->assertSame(5, $result->a);
        $this->assertSame(6, $result->b);
    }
}
