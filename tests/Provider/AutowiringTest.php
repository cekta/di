<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Autowiring;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Throwable;

class AutowiringTest extends TestCase
{
    /**
     * @var Autowiring
     */
    private $provider;
    /**
     * @var MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->provider = new Autowiring();
    }

    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, $this->provider);
    }

    public function testCanProvide(): void
    {
        $this->assertTrue($this->provider->canProvide(stdClass::class));
    }

    public function testCanProvideInvalidName(): void
    {
        $this->assertFalse($this->provider->canProvide('invalid name'));
    }

    public function testCanProvideInterface(): void
    {
        $this->assertFalse($this->provider->canProvide(Throwable::class));
    }

    public function getGetDependencies(): void
    {
        $this->assertSame([], $this->provider->getDependencies(stdClass::class));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideWithoutArguments(): void
    {
        assert($this->container instanceof ContainerInterface);
        $this->assertEquals(new stdClass(), $this->provider->provide(stdClass::class)($this->container));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideWithArguments(): void
    {
        $obj = new class (new stdClass(), '123')
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
        $this->container->method('get')->will($this->returnValueMap([
            [stdClass::class, new stdClass()],
            ['str', 'magic']
        ]));
        assert($this->container instanceof ContainerInterface);
        $result = $this->provider->provide($name)($this->container);
        $this->assertInstanceOf($name, $result);
        $this->assertSame('magic', $result->str);
        $this->assertSame([stdClass::class, 'str'], $this->provider->getDependencies($name));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideInvalidName()
    {
        $this->expectException(ClassNotCreated::class);
        $provider = new Autowiring();
        $provider->provide('invalid name');
    }
}
