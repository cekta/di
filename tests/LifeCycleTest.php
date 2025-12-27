<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\LazyClosure;
use Cekta\DI\Test\LifeCycleTest\Factory;
use Cekta\DI\Test\LifeCycleTest\FactorySubContainer;
use Cekta\DI\Test\LifeCycleTest\Singleton;
use Cekta\DI\Test\LifeCycleTest\SingletonSubContainer;
use Cekta\DI\Test\LifeCycleTest\SingletonSubContainer\Dependency;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

class LifeCycleTest extends TestCase
{
    private const FILE = __DIR__ . DIRECTORY_SEPARATOR . 'LifeCycleContainer.php';
    private const FQCN = 'Cekta\DI\Test\LifeCycleContainer';
    private const SCOPED_ALIAS = 'scoped_alias';
    private const SCOPED_DEFINITION = 'scoped_definition';
    private const SINGLETON_ALIAS = 'singleton_alias';
    private const SINGLETON_DEFINITION = 'singleton_definition';
    private const FACTORY_ALIAS = 'factory_alias';
    private const FACTORY_DEFINITION = 'factory_definition';
    private static ContainerInterface $container;
    private static ContainerInterface $container2;

    public static function setUpBeforeClass(): void
    {
        file_exists(self::FILE) && unlink(self::FILE);
    }

    protected function setUp(): void
    {
        if (file_exists(self::FILE)) {
            return;
        }
        $params = [
            self::SCOPED_DEFINITION => new LazyClosure(function () {
                return new stdClass();
            }),
            self::SINGLETON_DEFINITION => new LazyClosure(function () {
                return new stdClass();
            }),
            self::FACTORY_DEFINITION => new LazyClosure(function () {
                static $index = 0;
                return $index++;
            }),
        ];
        $compiler = new Compiler(
            containers: [
                stdClass::class,
                SingletonSubContainer::class,
                FactorySubContainer::class,
                Singleton::class,
                Factory::class,
                self::SCOPED_ALIAS,
                self::SINGLETON_ALIAS,
                self::FACTORY_ALIAS,
            ],
            params: $params,
            alias: [
                self::SCOPED_ALIAS => stdClass::class,
                self::SINGLETON_ALIAS => stdClass::class,
                self::FACTORY_ALIAS => self::FACTORY_DEFINITION,
            ],
            fqcn: self::FQCN,
            singletons: [
                Dependency::class,
                self::SINGLETON_ALIAS,
                self::SINGLETON_DEFINITION,
                Singleton::class,
            ],
            factories: [
                FactorySubContainer\Dependency::class,
                FactorySubContainer::class,
                Factory::class,
                self::FACTORY_ALIAS,
                self::FACTORY_DEFINITION,
            ]
        );
        file_put_contents(self::FILE, $compiler->compile());

        // @phpstan-ignore-next-line
        self::$container = new (self::FQCN)($params);
        // @phpstan-ignore-next-line
        self::$container2 = new (self::FQCN)($params);
    }

    public static function tearDownAfterClass(): void
    {
        file_exists(self::FILE) && unlink(self::FILE);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultAutowiredMustBeScoped(): void
    {
        $this->mustBeScoped(
            self::$container->get(stdClass::class),
            self::$container->get(stdClass::class),
            self::$container2->get(stdClass::class)
        );
    }

    private function mustBeScoped(mixed $v1, mixed $v2, mixed $v3): void
    {
        Assert::assertEquals(
            $v1,
            $v3
        );
        Assert::assertNotSame(
            $v1,
            $v3
        );
        Assert::assertSame($v1, $v2);
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
        Assert::assertSame(
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
        Assert::assertSame(
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
        Assert::assertSame(
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
        Assert::assertEquals($v1, $v2);
        Assert::assertNotSame($v1, $v2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFactoryAlias(): void
    {
        $v1 = self::$container->get(self::FACTORY_ALIAS);
        $v2 = self::$container->get(self::FACTORY_ALIAS);
        Assert::assertNotEquals($v1, $v2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFactoryDefinition(): void
    {
        $v1 = self::$container->get(self::FACTORY_DEFINITION);
        $v2 = self::$container->get(self::FACTORY_DEFINITION);
        Assert::assertNotEquals($v1, $v2);
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
        Assert::assertSame($life_cycle1->dependency, $life_cycle2->dependency);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLifeCycleForFactorySubContainer(): void
    {
        $r1 = self::$container->get(FactorySubContainer\Dependency::class);
        $r2 = self::$container->get(FactorySubContainer\Dependency::class);
        Assert::assertEquals($r1, $r2);
        Assert::assertNotSame($r1, $r2);
    }
}
