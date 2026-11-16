<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerFactory;
use Cekta\DI\ContainerGenerator;
use Cekta\DI\Test\LifeCycleTest\Factory;
use Cekta\DI\Test\LifeCycleTest\FactorySubContainer;
use Cekta\DI\Test\LifeCycleTest\Manifest;
use Cekta\DI\Test\LifeCycleTest\Singleton;
use Cekta\DI\Test\LifeCycleTest\SingletonSubContainer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

class LifeCycleTest extends TestCase
{
    private static Manifest $manifest;
    private static ContainerInterface $container;
    private static ContainerInterface $container2;

    public static function setUpBeforeClass(): void
    {
        self::$manifest = new Manifest(__DIR__ . DIRECTORY_SEPARATOR . 'LifeCycleContainer.php');
        file_exists(self::$manifest->filename()) && unlink(self::$manifest->filename());
    }

    protected function setUp(): void
    {
        if (file_exists(self::$manifest->filename())) {
            return;
        }
        $compiler = new ContainerGenerator();
        file_put_contents(self::$manifest->filename(), $compiler->generate(self::$manifest));
        $factory = new ContainerFactory();
        self::$container = $factory->build(self::$manifest);
        self::$container2 = $factory->build(self::$manifest);
    }

    public static function tearDownAfterClass(): void
    {
        file_exists(self::$manifest->filename()) && unlink(self::$manifest->filename());
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
            self::$container->get(self::$manifest::SCOPED_ALIAS),
            self::$container->get(self::$manifest::SCOPED_ALIAS),
            self::$container2->get(self::$manifest::SCOPED_ALIAS)
        );
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDefaultDefinitionMustBeScoped(): void
    {
        $this->mustBeScoped(
            self::$container->get(self::$manifest::SCOPED_DEFINITION),
            self::$container->get(self::$manifest::SCOPED_DEFINITION),
            self::$container2->get(self::$manifest::SCOPED_DEFINITION)
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
            self::$container->get(self::$manifest::SINGLETON_ALIAS),
            self::$container2->get(self::$manifest::SINGLETON_ALIAS)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSingletonDefinition(): void
    {
        Assert::assertSame(
            self::$container->get(self::$manifest::SINGLETON_DEFINITION),
            self::$container2->get(self::$manifest::SINGLETON_DEFINITION)
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
        $v1 = self::$container->get(self::$manifest::FACTORY_ALIAS);
        $v2 = self::$container->get(self::$manifest::FACTORY_ALIAS);
        Assert::assertNotEquals($v1, $v2);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testFactoryDefinition(): void
    {
        $v1 = self::$container->get(self::$manifest::FACTORY_DEFINITION);
        $v2 = self::$container->get(self::$manifest::FACTORY_DEFINITION);
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
