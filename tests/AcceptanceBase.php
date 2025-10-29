<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\Lazy;
use Cekta\DI\Test\AcceptanceTest\A;
use Cekta\DI\Test\AcceptanceTest\C;
use Cekta\DI\Test\AcceptanceTest\ContainerCreatedWithNew;
use Cekta\DI\Test\AcceptanceTest\EntrypointAutowiring;
use Cekta\DI\Test\AcceptanceTest\EntrypointSharedDependency;
use Cekta\DI\Test\AcceptanceTest\EntrypointVariadicClass;
use Cekta\DI\Test\AcceptanceTest\I;
use Cekta\DI\Test\AcceptanceTest\R1;
use Cekta\DI\Test\AcceptanceTest\S;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

abstract class AcceptanceBase extends TestCase
{
    protected ContainerInterface $container;

    /**
     * @var array<string, mixed|Lazy>
     */
    protected array $params = [];
    /**
     * @var array<string, string>
     */
    protected array $alias = [
        I::class => R1::class,
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
    ];

    protected function setUp(): void
    {
        $this->params = [
            'username' => 'some username',
            'password' => 'some password',
            'db_username' => 'some db username',
            S::class . '|string' => 'named params: ' . S::class . '|string',
            '...variadic_int' => [1, 3, 5],
            '...' . A::class => [new A(), new A()],
            'dsn' => new Lazy(function (ContainerInterface $container) {
                /** @var string $username */
                $username = $container->get('username');
                /** @var string $password */
                $password = $container->get('password');
                return "definition u: $username, p: $password";
            })
        ];

        $this->container = $this->makeContainer();
    }

    abstract protected function makeContainer(): ContainerInterface;

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
            EntrypointSharedDependency::class,
            $autowiring->exampleShared,
            'other entrypoint must be correct inject'
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
            $this->params['...variadic_int'],
            $autowiring->variadic_int,
            'variadic params must be inject'
        );
        $this->assertInstanceOf(
            S::class,
            $autowiring->s
        );
        $this->assertSame(
            $autowiring->s,
            $autowiring->s2,
            'must be auto shared between one entrypoint'
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
     */
    public function testNotSharedMustCreatedByNew(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get(ContainerCreatedWithNew::class);
    }
}
