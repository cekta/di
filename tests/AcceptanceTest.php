<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Exception\NotInstantiable;
use Cekta\DI\LoaderDTO;
use Cekta\DI\Rule\Regex;
use Cekta\DI\Test\AcceptanceTest\A;
use Cekta\DI\Test\AcceptanceTest\AutowiringInConstructor;
use Cekta\DI\Test\AcceptanceTest\AutowiringShared;
use Cekta\DI\Test\AcceptanceTest\D;
use Cekta\DI\Test\AcceptanceTest\ExampleApplyRule;
use Cekta\DI\Test\AcceptanceTest\ExamplePopShared;
use Cekta\DI\Test\AcceptanceTest\I;
use Cekta\DI\Test\AcceptanceTest\R1;
use Cekta\DI\Test\AcceptanceTest\S;
use Cekta\DI\Test\AcceptanceTest\Shared;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class AcceptanceTest extends TestCase
{
    private const FILE = __DIR__ . '/Container.php';
    private const FQCN = 'Cekta\DI\Test\Container';
    private static ContainerInterface $container;
    private const TARGETS = [
        Shared::class,
        AutowiringInConstructor::class,
        stdClass::class,
        AutowiringShared::class,
        ExamplePopShared::class,
        ExampleApplyRule::class,
    ];
    private const PARAMS = [
        'username' => 'some username',
        'password' => 'some password',
        'db_username' => 'some db username',
        S::class . '|string' => 'named params: ' . S::class . '|string',
        '...variadic_int' => [1, 3, 5],
    ];
    /**
     * @var array<string, callable>
     */
    private static array $definitions = [];
    private const ALIAS = [
        I::class => R1::class,
    ];

    public static function tearDownAfterClass(): void
    {
        unlink(self::FILE);
    }

    /**
     * @throws IOExceptionInterface
     * @throws NotInstantiable
     * @throws InvalidContainerForCompile
     * @throws InfiniteRecursion
     */
    protected function setUp(): void
    {
        self::$definitions = [
            'dsn' => function (ContainerInterface $container) {
                /** @var string $username */
                $username = $container->get('username');
                /** @var string $password */
                $password = $container->get('password');
                return "definition u: $username, p: $password";
            }
        ];
        self::$container = Container::build(
            filename: self::FILE,
            loader: function () {
                return new LoaderDTO(
                    containers: self::TARGETS,
                    alias: self::ALIAS,
                    rule: new Regex('/ExampleApplyRule/', ['username' => 'db_username'])
                );
            },
            params: self::PARAMS,
            definitions: self::$definitions,
            fqcn: self::FQCN,
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNotFound(): void
    {
        $key = 'not exist container';
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage("Container `$key` not found");
        self::$container->get($key);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutowiring(): void
    {
        /** @var AutowiringInConstructor $autowiring */
        $autowiring = self::$container->get(AutowiringInConstructor::class);
        $this->assertInstanceOf(AutowiringInConstructor::class, $autowiring);
        $this->assertInstanceOf(A::class, $autowiring->a);
        $this->assertSame(self::PARAMS['username'], $autowiring->username);
        $this->assertSame(self::PARAMS['password'], $autowiring->password);
        $this->assertSame(self::PARAMS[S::class . '|string'], $autowiring->union_type);
        $definition = self::$definitions['dsn'];
        $this->assertSame($definition(self::$container), $autowiring->dsn);
        $this->assertInstanceOf(R1::class, $autowiring->i);
        $this->assertInstanceOf(AutowiringShared::class, self::$container->get(AutowiringShared::class));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShared(): void
    {
        /** @var Shared $example */
        $example = self::$container->get(Shared::class);
        $this->assertInstanceOf(Shared::class, $example);
        $this->assertSame($example, self::$container->get(Shared::class));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSharedDependencyMustBeSame(): void
    {
        /** @var Shared $shared */
        $shared = self::$container->get(Shared::class);
        /** @var AutowiringInConstructor $autowiring */
        $autowiring = self::$container->get(AutowiringInConstructor::class);
        $this->assertSame($shared->s, $autowiring->s);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutowiringWithoutArguments(): void
    {
        /** @var stdClass $obj */
        $obj = self::$container->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $obj);
        $this->assertSame($obj, self::$container->get(stdClass::class));
    }

    public function testHas(): void
    {
        foreach (self::TARGETS as $key) {
            $this->assertTrue(self::$container->has($key));
        }

        foreach (array_keys(self::PARAMS) as $key) {
            $this->assertFalse(self::$container->has($key));
        }

        foreach (array_keys(self::$definitions) as $key) {
            $this->assertFalse(self::$container->has($key));
        }
    }

    public function testWithoutRequiredParams(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Containers: %s, %s, %s|string, %s, %s must be declared in params or definitions',
                'username',
                'password',
                S::class,
                '...variadic_int',
                'db_username',
            )
        );
        // @phpstan-ignore class.notFound
        new (self::FQCN)([], self::$definitions);
    }

    public function testWithoutRequiredDefinitions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Containers: dsn must be declared in params or definitions');
        // @phpstan-ignore class.notFound
        new (self::FQCN)(self::PARAMS, []);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testNotSharedMustCreatedByNew(): void
    {
        $this->assertFalse(self::$container->has(D::class));
        $this->expectException(NotFoundExceptionInterface::class);
        self::$container->get(D::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testPopSharedTest(): void
    {
        $this->assertInstanceOf(ExamplePopShared::class, self::$container->get(ExamplePopShared::class));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testApplyRule(): void
    {
        /** @var ExampleApplyRule $obj */
        $obj = self::$container->get(ExampleApplyRule::class);
        $this->assertSame(self::PARAMS['db_username'], $obj->username);
        $this->assertSame(self::PARAMS['password'], $obj->password);
    }
}
