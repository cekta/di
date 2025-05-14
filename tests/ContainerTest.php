<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Test\Fixture\A;
use Cekta\DI\Test\Fixture\Example\Autowiring;
use Cekta\DI\Test\Fixture\Example\AutowiringShared;
use Cekta\DI\Test\Fixture\Example\Shared;
use Cekta\DI\Test\Fixture\Example\WithoutArgument;
use Cekta\DI\Test\Fixture\I;
use Cekta\DI\Test\Fixture\R1;
use Cekta\DI\Test\Fixture\S;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    private const FILE = __DIR__ . '/Container.php';
    private ContainerInterface $container;
    private const TARGETS = [
        Shared::class,
        Autowiring::class,
        WithoutArgument::class,
        AutowiringShared::class,
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
    private string $fqcn = 'Cekta\DI\Test\Container';

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

    protected function setUp(): void
    {
        if (!file_exists($this::FILE)) {
            file_put_contents(
                $this::FILE,
                new Compiler(
                    params: $this::PARAMS,
                    definitions: $this::$definitions,
                    alias: $this::ALIAS,
                    containers: $this::TARGETS,
                    fqcn: $this->fqcn,
                )
            );
        }
        $container = new $this->fqcn(
            params: $this::PARAMS,
            definitions: $this::$definitions,
        );
        /** @var ContainerInterface $container */
        $this->container = $container;
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
        $this->assertInstanceOf(AutowiringShared::class, $this->container->get(AutowiringShared::class));
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

    public function testHas(): void
    {
        foreach (self::TARGETS as $key) {
            $this->assertTrue($this->container->has($key));
        }

        foreach (array_keys(self::PARAMS) as $key) {
            $this->assertFalse($this->container->has($key));
        }

        foreach (array_keys(self::$definitions) as $key) {
            $this->assertFalse($this->container->has($key));
        }
    }

    public function testWithoutRequiredParams(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'params: username, password, Cekta\DI\Test\Fixture\S|string, ...variadic_int must be declared'
        );
        new $this->fqcn([], self::$definitions);
    }

    public function testWithoutRequiredDefinitions(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('definitions: definition must be declared');
        new $this->fqcn(self::PARAMS, []);
    }
}
