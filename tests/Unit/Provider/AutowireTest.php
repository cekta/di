<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowire;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Throwable;

/**
 * @covers \Cekta\DI\Provider\Autowire
 */
class AutowireTest extends TestCase
{
    public function testHasProvide(): void
    {
        $provider = new Autowire();
        static::assertTrue($provider->canProvide(stdClass::class));
        static::assertFalse($provider->canProvide('invalid name'));
        static::assertFalse($provider->canProvide(Throwable::class));
    }

    public function testProvideWithoutArguments(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        static::assertEquals(new stdClass(), (new Autowire())
            ->provide(stdClass::class, $container));
    }

    public function testProvideInvalidName(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('Container `magic` not found');

        (new Autowire())->provide('magic', $container);
    }

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
        $result = (new Autowire())->provide($name, $container);
        static::assertInstanceOf($name, $result);
        static::assertSame('magic', $result->str);
    }
}
