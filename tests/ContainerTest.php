<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\InvalidConfiguration;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Test\Fixture\A;
use Cekta\DI\Test\Fixture\Example\Autowiring;
use Cekta\DI\Test\Fixture\Example\Shared;
use Cekta\DI\Test\Fixture\Example\WithoutArgument;
use Cekta\DI\Test\Fixture\I;
use Cekta\DI\Test\Fixture\R1;
use Cekta\DI\Test\Fixture\S;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class ContainerTest extends TestCase
{
    private const FILE = __DIR__ . '/Container.php';
    private ContainerInterface $container;
    private const CONTAINERS = [
        Shared::class,
        Autowiring::class,
        WithoutArgument::class,
    ];
    private const PARAMS = [
        'username' => 'some username',
        'password' => 'some password',
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
//        unlink(self::FILE);
    }

    public static function setUpBeforeClass(): void
    {
        file_exists(self::FILE) && unlink(self::FILE);
        self::$definitions = [
            'definition' => function (ContainerInterface $container) {
                /** @var string $username */
                $username = $container->get('username');
                /** @var string $password */
                $password = $container->get('password');
                return "definition u: $username, p: $password";
            }
        ];
    }

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        if (!file_exists($this::FILE)) {
            $compiler = new Compiler();
            $compile = $compiler->compile(
                targets: $this::CONTAINERS,
                alias: $this::ALIAS,
                params: $this::PARAMS,
                definitions: $this::$definitions,
                fqcn: 'Cekta\DI\Test\Container',
            );
            file_put_contents($this::FILE, $compile);
        }
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->container = new Container(
            params: $this::PARAMS,
            alias: $this::ALIAS,
            definitions: $this::$definitions,
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
        $this->container->get($key);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutowiring(): void
    {
        /** @var Autowiring $autowiring */
        $autowiring = $this->container->get(Autowiring::class);
        $this->assertInstanceOf(Autowiring::class, $autowiring);
        $this->assertInstanceOf(A::class, $autowiring->a);
        $this->assertSame(self::PARAMS['username'], $autowiring->username);
        $this->assertSame(self::PARAMS['password'], $autowiring->password);
        $this->assertSame(self::PARAMS[S::class . '|string'], $autowiring->named);
        $definition = self::$definitions['definition'];
        $this->assertSame($definition($this->container), $autowiring->definition);
        $this->assertInstanceOf(R1::class, $autowiring->i);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShared(): void
    {
        /** @var Shared $example */
        $example = $this->container->get(Shared::class);
        $this->assertInstanceOf(Shared::class, $example);
        $this->assertSame($example, $this->container->get(Shared::class));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testSharedDependencyMustBeSame(): void
    {
        /** @var Shared $shared */
        $shared = $this->container->get(Shared::class);
        /** @var Autowiring $autowiring */
        $autowiring = $this->container->get(Autowiring::class);
        $this->assertSame($shared->s, $autowiring->s);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutowiringWithoutArguments(): void
    {
        /** @var WithoutArgument $without_argument */
        $without_argument = $this->container->get(WithoutArgument::class);
        $this->assertInstanceOf(WithoutArgument::class, $without_argument);
        $this->assertSame($without_argument, $this->container->get(WithoutArgument::class));
    }

    public function testCreateWithoutRequiredParams(): void
    {
        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage(
            sprintf(
                'Container: %s must be defined',
                implode(
                    ', ',
                    array_unique(
                        array_merge(
                            array_keys($this::PARAMS),
                            array_keys($this::$definitions),
                            array_keys($this::ALIAS),
                        )
                    )
                )
            )
        );
        /** @noinspection PhpUndefinedClassInspection */
        new Container();
    }

    public function testHas(): void
    {
        /** @var string[] $keys */
        $keys = array_unique(
            array_merge(
                array_keys($this::PARAMS),
                array_keys($this::$definitions),
                array_keys($this::ALIAS),
            )
        );
        foreach ($keys as $key) {
            $this->assertTrue($this->container->has($key));
        }
    }
}
