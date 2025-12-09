<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\CircularDependency as CircularDependencyException;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\LazyClosure;
use Cekta\DI\Test\AcceptanceTest\A;
use Cekta\DI\Test\AcceptanceTest\CircularDependency;
use Cekta\DI\Test\AcceptanceTest\ContainerCreatedWithNew;
use Cekta\DI\Test\AcceptanceTest\EntrypointAutowiring;
use Cekta\DI\Test\AcceptanceTest\EntrypointCircularDependency;
use Cekta\DI\Test\AcceptanceTest\EntrypointOverwriteExtendConstructor;
use Cekta\DI\Test\AcceptanceTest\EntrypointSharedDependency;
use Cekta\DI\Test\AcceptanceTest\EntrypointVariadicClass;
use Cekta\DI\Test\AcceptanceTest\I;
use Cekta\DI\Test\AcceptanceTest\R1;
use Cekta\DI\Test\AcceptanceTest\S;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

class AcceptanceTest extends TestCase
{
    private bool $is_compiled = false;
    protected ContainerInterface $container;

    /**
     * @var array<string, mixed|LazyClosure>
     */
    protected array $params = [];
    /**
     * @var array<string, string>
     */
    protected array $alias = [
        I::class => R1::class,
        'argument_to_custom_alias' => 'argument_to_custom_alias_value',
        EntrypointSharedDependency::class . '$argument_to_custom_alias' => 'argument_to_custom_alias_custom_value',
        EntrypointSharedDependency::class . '$argument_to_custom_alias2' => 'argument_to_custom_alias_custom_value',
    ];

    protected string $file = __DIR__ . '/AcceptanceTest/Container.php';
    protected string $fqcn = 'Cekta\DI\Test\AcceptanceTest\Container';

    /**
     * @var string[]
     */
    protected array $containers = [
        stdClass::class, // example class without dependencies
        EntrypointAutowiring::class,
        EntrypointSharedDependency::class,
        EntrypointVariadicClass::class,
        EntrypointOverwriteExtendConstructor::class,
    ];

    protected function setUp(): void
    {
        $this->params = [
            'username' => 'some username',
            'password' => 'some password',
            EntrypointOverwriteExtendConstructor::class . '$username' => 'base constructor overwritten username',
            'argument_to_custom_param' => 'default param',
            EntrypointSharedDependency::class . '$argument_to_custom_param' => 'custom value param',
            'argument_to_custom_alias_value' => 'default value for alias',
            'argument_to_custom_alias_custom_value' => 'custom value for alias',
            'db_username' => 'some db username',
            S::class . '|string' => 'named params: ' . S::class . '|string',
            '...variadic_int' => [1, 3, 5],
            '...' . EntrypointSharedDependency::class . '$variadic_int' => [9, 8, 7],
            '...' . A::class => [new A(), new A()],
            'dsn' => new LazyClosure(function (ContainerInterface $container) {
                /** @var string $username */
                $username = $container->get('username');
                /** @var string $password */
                $password = $container->get('password');
                return "definition u: $username, p: $password";
            })
        ];

        if (!$this->is_compiled) {
            $compiler = new Compiler(
                containers: $this->containers,
                params: $this->params,
                alias: $this->alias,
                fqcn: $this->fqcn,
            );
            file_put_contents($this->file, $compiler->compile());
        }

        $this->container = new ($this->fqcn)($this->params);
    }

    protected function tearDown(): void
    {
        unlink($this->file);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAllContainersMustBeAvailableAndGettable(): void
    {
        foreach ($this->containers as $key) {
            $this->assertTrue($this->container->has($key), 'available for get');
            $this->assertNotEmpty($this->container->get($key), 'all containers must be gettable');
        }
        $this->assertFalse($this->container->has('invalid name'));
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
        /** @var EntrypointAutowiring $autowiring */
        $autowiring = $this->container->get(EntrypointAutowiring::class);
        $this->assertInstanceOf(EntrypointAutowiring::class, $autowiring);
        $this->assertSame(
            $this->params['username'],
            $autowiring->username,
            'string(primitive) params must be inject'
        );
        $this->assertSame(
            $this->params['password'],
            $autowiring->password,
            'string(primitive) params must be inject'
        );

        $this->assertInstanceOf(
            ContainerCreatedWithNew::class,
            $autowiring->created_with_new,
            'autowiring dependency must be correct'
        );
        $this->assertInstanceOf(
            R1::class,
            $autowiring->i,
            'alias for interface must be correct resolved'
        );
        $this->assertInstanceOf(
            S::class,
            $autowiring->s,
            'autowiring first dependency',
        );
        $this->assertSame(
            $autowiring->s,
            $autowiring->s2,
            'must be auto shared between one entrypoint'
        );
        $this->assertSame(
            $autowiring->s,
            $autowiring->s3,
            'must be called array_pop on shared',
        );
        $this->assertSame(
            $autowiring->s,
            $autowiring->s4,
            'must be called array_pop on everytime',
        );
        $this->assertSame(
            $this->params[S::class . '|string'],
            $autowiring->union_type,
            'union|dfn params must work'
        );
        $this->assertSame(
            'definition u: some username, p: some password',
            $autowiring->dsn,
            'lazy loading params must be correct inject'
        );
        $this->assertSame(
            $this->params['argument_to_custom_param'],
            $autowiring->argument_to_custom_param,
            'must default value from param, no custom param'
        );
        $this->assertSame(
            $this->params['argument_to_custom_alias_value'],
            $autowiring->argument_to_custom_alias,
            'must default alias, no custom alias'
        );
        $this->assertInstanceOf(
            EntrypointSharedDependency::class,
            $autowiring->exampleShared,
            'other entrypoint must be correct inject'
        );
        $this->assertSame(
            $this->params['...variadic_int'],
            $autowiring->variadic_int,
            'variadic params must be inject'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAuthShareDependencyBetweenEntrypoint(): void
    {
        /** @var EntrypointSharedDependency $entrypoint_shared */
        $entrypoint_shared = $this->container->get(EntrypointSharedDependency::class);
        $this->assertInstanceOf(EntrypointSharedDependency::class, $entrypoint_shared);
        /** @var EntrypointAutowiring $autowiring */
        $autowiring = $this->container->get(EntrypointAutowiring::class);
        $this->assertSame(
            $entrypoint_shared->s,
            $autowiring->s,
            'dependency between few entrypoint must be auto shared (same)'
        );
        $this->assertSame(
            $this->params[EntrypointSharedDependency::class . '$argument_to_custom_param'],
            $entrypoint_shared->argument_to_custom_param,
            'must be set custom param only for this class'
        );
        $this->assertSame(
            $this->params['argument_to_custom_alias_custom_value'],
            $entrypoint_shared->argument_to_custom_alias,
            'must be used custom alias only for this class'
        );
        $this->assertSame(
            $this->params['argument_to_custom_alias_custom_value'],
            $entrypoint_shared->argument_to_custom_alias2,
            'after alias must be correct detect param with array_pop stack'
        );
        $this->assertSame(
            $this->params['...' . EntrypointSharedDependency::class . '$variadic_int'],
            $entrypoint_shared->variadic_int
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutowiringWithoutArguments(): void
    {
        /** @var stdClass $obj */
        $obj = $this->container->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $obj);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutowiringVariadicClass(): void
    {
        /** @var EntrypointVariadicClass $obj */
        $obj = $this->container->get(EntrypointVariadicClass::class);
        $this->assertSame(
            $this->params['...' . A::class],
            $obj->a_array,
            'variadic params without primitive must be correct injected'
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testOverwrittenExtendConstructor(): void
    {
        /** @var EntrypointOverwriteExtendConstructor $obj */
        $obj = $this->container->get(EntrypointOverwriteExtendConstructor::class);
        $this->assertSame(
            $this->params[EntrypointOverwriteExtendConstructor::class . '$username'],
            $obj->username,
            'value from base constructor must be overwritten by custom rule '
        );
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testNotSharedMustCreatedByNew(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get(ContainerCreatedWithNew::class);
    }

    public function testWithoutRequiredParams(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Containers: %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s must be declared in params',
                'username',
                'password',
                S::class . '|string',
                'dsn',
                'argument_to_custom_param',
                'argument_to_custom_alias_value',
                EntrypointSharedDependency::class . '$argument_to_custom_param',
                'argument_to_custom_alias_custom_value',
                '...' . EntrypointSharedDependency::class . '$variadic_int',
                '...variadic_int',
                '...' . A::class,
                EntrypointOverwriteExtendConstructor::class . '$username',
            )
        );
        new ($this->fqcn)([]);
    }

    public function testInfiniteRecursion(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage(
            sprintf(
                '`%s` has circular dependency, stack: %s, %s',
                EntrypointCircularDependency::class,
                EntrypointCircularDependency::class,
                CircularDependency::class
            )
        );

        (new Compiler(containers: [EntrypointCircularDependency::class]))->compile();
    }
}
