<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\ContainerFactory;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\Test\Fixture\Example\WithoutArgument;
use Cekta\DI\Test\LifeCycle\Factory;
use Cekta\DI\Test\LifeCycle\FactorySubContainer;
use Cekta\DI\Test\LifeCycle\Singleton;
use Cekta\DI\Test\LifeCycle\SingletonSubContainer;
use Cekta\DI\Test\LifeCycle\SingletonSubContainer\Dependency;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class LifeCycleTest extends TestCase
{
    private static ContainerInterface $container;
    private static ContainerInterface $container2;

    private const FILE = __DIR__ . DIRECTORY_SEPARATOR . 'LifeCycleContainer.php';
    private const FQCN = 'Cekta\DI\Test\LifeCycleContainer';
    private const SCOPED_ALIAS = 'scoped_alias';
    private const SCOPED_DEFINITION = 'scoped_definition';
    private const SINGLETON_ALIAS = 'singleton_alias';
    private const SINGLETON_DEFINITION = 'singleton_definition';
    private const FACTORY_ALIAS = 'factory_alias';
    private const FACTORY_DEFINITION = 'factory_definition';

    /**
     * @throws IOExceptionInterface
     * @throws InfiniteRecursion
     * @throws NotInstantiable
     * @throws InvalidContainerForCompile
     */
    protected function setUp(): void
    {
        $factory = new ContainerFactory();
        $params = [
            'filename' => self::FILE,
            'fqcn' => self::FQCN,
            'force_compile' => true,
            'containers' => [
                WithoutArgument::class,
                SingletonSubContainer::class,
                FactorySubContainer::class,
                Singleton::class,
                Factory::class,
            ],
            'alias' => [
                self::SCOPED_ALIAS => WithoutArgument::class,
                self::SINGLETON_ALIAS => WithoutArgument::class,
                self::FACTORY_ALIAS => self::FACTORY_DEFINITION,
            ],
            'definitions' => [
                self::SCOPED_DEFINITION => function () {
                    return new stdClass();
                },
                self::SINGLETON_DEFINITION => function () {
                    return new stdClass();
                },
                self::FACTORY_DEFINITION => function () {
                    static $index = 0;
                    return $index++;
                },
            ],
            'singletons' => [
                Dependency::class,
                self::SINGLETON_ALIAS,
                self::SINGLETON_DEFINITION,
                Singleton::class,
            ],
            'factories' => [
                FactorySubContainer\Dependency::class,
                FactorySubContainer::class,
                Factory::class,
                self::FACTORY_ALIAS,
                self::FACTORY_DEFINITION,
            ]
        ];
        self::$container = $factory->make(...$params);
        self::$container2 = $factory->make(...$params);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::FILE);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultAutowiredMustBeScoped(): void
    {
        $this->mustBeScoped(
            self::$container->get(WithoutArgument::class),
            self::$container->get(WithoutArgument::class),
            self::$container2->get(WithoutArgument::class)
        );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultAliasMustBeScoped(): void
    {
        $this->mustBeScoped(
            self::$container->get(self::SCOPED_ALIAS),
            self::$container->get(self::SCOPED_ALIAS),
            self::$container2->get(self::SCOPED_ALIAS)
        );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultDefinitionMustBeScoped(): void
    {
        $this->mustBeScoped(
            self::$container->get(self::SCOPED_DEFINITION),
            self::$container->get(self::SCOPED_DEFINITION),
            self::$container2->get(self::SCOPED_DEFINITION)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSingletonAutowiring(): void
    {
        $this->assertSame(
            self::$container->get(Singleton::class),
            self::$container2->get(Singleton::class)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSingletonAlias(): void
    {
        $this->assertSame(
            self::$container->get(self::SINGLETON_ALIAS),
            self::$container2->get(self::SINGLETON_ALIAS)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSingletonDefinition(): void
    {
        $this->assertSame(
            self::$container->get(self::SINGLETON_DEFINITION),
            self::$container2->get(self::SINGLETON_DEFINITION)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFactoryAutowiring(): void
    {
        $v1 = self::$container->get(Factory::class);
        $v2 = self::$container->get(Factory::class);
        $this->assertEquals($v1, $v2);
        $this->assertNotSame($v1, $v2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFactoryAlias(): void
    {
        $v1 = self::$container->get(self::FACTORY_ALIAS);
        $v2 = self::$container->get(self::FACTORY_ALIAS);
        $this->assertNotEquals($v1, $v2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFactoryDefinition(): void
    {
        $v1 = self::$container->get(self::FACTORY_DEFINITION);
        $v2 = self::$container->get(self::FACTORY_DEFINITION);
        $this->assertNotEquals($v1, $v2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLifeCycleForSingletonSubContainer(): void
    {
        /** @var SingletonSubContainer $life_cycle1 */
        $life_cycle1 = self::$container->get(SingletonSubContainer::class);
        /** @var SingletonSubContainer $life_cycle2 */
        $life_cycle2 = self::$container2->get(SingletonSubContainer::class);
        $this->assertSame($life_cycle1->dependency, $life_cycle2->dependency);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLifeCycleForFactorySubContainer(): void
    {
        $r1 = self::$container->get(FactorySubContainer\Dependency::class);
        $r2 = self::$container->get(FactorySubContainer\Dependency::class);
        $this->assertEquals($r1, $r2);
        $this->assertNotSame($r1, $r2);
    }

    private function mustBeScoped(mixed $v1, mixed $v2, mixed $v3): void
    {
        $this->assertEquals(
            $v1,
            $v3
        );
        $this->assertNotSame(
            $v1,
            $v3
        );
        $this->assertSame($v1, $v2);
    }
}
