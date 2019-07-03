<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowire;
use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;
use Cekta\DI\ProviderNotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Throwable;

/**
 * @covers \Cekta\DI\Provider\Autowire
 */
class AutowireTest extends TestCase
{
    public function testHasProvide(): void
    {
        $reader = $this->createMock(Autowire\ReaderInterface::class);
        assert($reader instanceof Autowire\ReaderInterface);
        $provider = new Autowire($reader);
        static::assertTrue($provider->canProvide(stdClass::class));
        static::assertFalse($provider->canProvide('invalid name'));
        static::assertFalse($provider->canProvide(Throwable::class));
    }

    /**
     * @throws ProviderNotFoundException
     */
    public function testProvideWithoutArguments(): void
    {
        $reader = $this->createMock(Autowire\ReaderInterface::class);
        assert($reader instanceof Autowire\ReaderInterface);
        $autowire = new Autowire($reader);
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        static::assertEquals(new stdClass(), $autowire->provide(stdClass::class, $container));
    }

    /**
     * @throws ProviderNotFoundException
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
        $reader = $this->createMock(Autowire\ReaderInterface::class);
        $reader->expects($this->once())->method('getDependencies')
            ->with($name)
            ->willReturn([stdClass::class, 'str']);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->will($this->returnValueMap([
                [stdClass::class, new stdClass()],
                ['str', 'magic']
            ]));
        assert($container instanceof ContainerInterface);
        assert($reader instanceof Autowire\ReaderInterface);
        $result = (new Autowire($reader))->provide($name, $container);
        static::assertInstanceOf($name, $result);
        static::assertSame('magic', $result->str);
    }

    /**
     * @throws ProviderNotFoundException
     */
    public function testProvideInvalidName(): void
    {
        $this->expectException(ProviderNotFoundException::class);
        $this->expectExceptionMessage('Container `magic` not found');

        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);

        $exception = $this->createMock(InvalidClassName::class);
        $reader = $this->createMock(Autowire\ReaderInterface::class);
        assert($exception instanceof InvalidClassName);
        $reader->expects($this->once())->method('getDependencies')
            ->with('magic')
            ->willThrowException($exception);
        assert($reader instanceof Autowire\ReaderInterface);
        (new Autowire($reader))->provide('magic', $container);
    }
}
