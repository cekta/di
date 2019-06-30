<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider;

use Cekta\DI\Provider\Autowire;
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
        $provider = new Autowire();
        static::assertTrue($provider->canProvide(stdClass::class));
        static::assertFalse($provider->canProvide('invalid name'));
        static::assertFalse($provider->canProvide(Throwable::class));
    }

    /**
     * @throws ProviderNotFoundException
     */
    public function testProvideWithoutArguments(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        assert($container instanceof ContainerInterface);
        static::assertEquals(new stdClass(), (new Autowire())
            ->provide(stdClass::class, $container));
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

        (new Autowire())->provide('magic', $container);
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

    /**
     * @throws ProviderNotFoundException
     */
    public function testGetWithRuleForContainer()
    {
        $obj = new class('123')
        {
            public $path;

            public function __construct(string $path)
            {
                $this->path = $path;
            }
        };
        $name = get_class($obj);
        $rule = $this->createMock(Autowire\RuleInterface::class);
        $rule->expects($this->once())->method('acceptable')
            ->with($name)
            ->willReturn(true);
        $rule->expects($this->once())->method('accept')
            ->willReturn(['path' => 'magic.path']);
        assert($rule instanceof Autowire\RuleInterface);
        $autowire = new Autowire($rule);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')
            ->with('magic.path')
            ->willReturn('some value');
        assert($container instanceof ContainerInterface);
        $result = $autowire->provide($name, $container);
        static::assertSame('some value', $result->path);
    }
}
