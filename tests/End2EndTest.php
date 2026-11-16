<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerFactory;
use Cekta\DI\ContainerGenerator;
use Cekta\DI\Manifest;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Test\ContainerBuilderTest\A;
use Cekta\DI\Test\ContainerBuilderTest\CircularDependency;
use Cekta\DI\Test\ContainerBuilderTest\ContainerCreatedWithNew;
use Cekta\DI\Test\ContainerBuilderTest\EntrypointAutowiring;
use Cekta\DI\Test\ContainerBuilderTest\EntrypointCircularDependency;
use Cekta\DI\Test\ContainerBuilderTest\EntrypointOptionalArgument;
use Cekta\DI\Test\ContainerBuilderTest\EntrypointOverwriteExtendConstructor;
use Cekta\DI\Test\ContainerBuilderTest\EntrypointSharedDependency;
use Cekta\DI\Test\ContainerBuilderTest\EntrypointVariadicClass;
use Cekta\DI\Test\ContainerBuilderTest\I;
use Cekta\DI\Test\ContainerBuilderTest\ProjectEnd2End;
use Cekta\DI\Test\ContainerBuilderTest\R1;
use Cekta\DI\Test\ContainerBuilderTest\S;
use Cekta\DI\Test\ContainerBuilderTest\SWithParam;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

class End2EndTest extends TestCase
{
    private ContainerInterface $container;
    private static ProjectEnd2End $manifest;

    public static function setUpBeforeClass(): void
    {
        self::$manifest = new ProjectEnd2End(__DIR__ . "/ContainerBuilderTest/Container.php");
        file_exists(self::$manifest->filename()) &&
            unlink(self::$manifest->filename());
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        if (!file_exists(self::$manifest->filename())) {
            $compiler = new ContainerGenerator();
            file_put_contents(self::$manifest->filename(), $compiler->generate(self::$manifest));
        }
        $factory = new ContainerFactory();
        $this->container = $factory->build(self::$manifest);
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        file_exists(self::$manifest->filename()) &&
            unlink(self::$manifest->filename());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAllEntriesMustBeAvailableAndGettable(): void
    {
        foreach (self::$manifest->definition()->entries as $key) {
            Assert::assertTrue(
                $this->container->has($key),
                "available for get",
            );
            Assert::assertNotEmpty(
                $this->container->get($key),
                "all entries must be gettable",
            );
        }
        Assert::assertFalse($this->container->has("invalid name"));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNotFound(): void
    {
        $key = "not exist container";
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
        Assert::assertInstanceOf(EntrypointAutowiring::class, $autowiring);
        Assert::assertSame(
            self::$manifest->params()["username"],
            $autowiring->username,
            "string(primitive) params must be inject",
        );
        Assert::assertSame(
            self::$manifest->params()["password"],
            $autowiring->password,
            "string(primitive) params must be inject",
        );

        Assert::assertInstanceOf(
            ContainerCreatedWithNew::class,
            $autowiring->created_with_new,
            "autowiring dependency must be correct",
        );
        Assert::assertInstanceOf(
            R1::class,
            $autowiring->i,
            "alias for interface must be correct resolved",
        );
        Assert::assertInstanceOf(
            S::class,
            $autowiring->s,
            "autowiring first dependency",
        );
        Assert::assertSame(
            $autowiring->s,
            $autowiring->s2,
            "must be auto shared between one entrypoint",
        );
        Assert::assertSame(
            $autowiring->s,
            $autowiring->s3,
            "must be called array_pop on shared",
        );
        Assert::assertSame(
            $autowiring->s,
            $autowiring->s4,
            "must be called array_pop on everytime",
        );
        Assert::assertSame(
            self::$manifest->params()[S::class . "|string"],
            $autowiring->union_type,
            "union|dfn params must work",
        );
        Assert::assertSame(
            "definition u: some username, p: some password",
            $autowiring->dsn,
            "lazy loading params must be correct inject",
        );
        Assert::assertSame(
            self::$manifest->params()["argument_to_custom_param"],
            $autowiring->argument_to_custom_param,
            "must default value from param, no custom param",
        );
        Assert::assertSame(
            self::$manifest->params()["argument_to_custom_alias_value"],
            $autowiring->argument_to_custom_alias,
            "must default alias, no custom alias",
        );
        Assert::assertInstanceOf(
            EntrypointSharedDependency::class,
            $autowiring->exampleShared,
            "other entrypoint must be correct inject",
        );
        Assert::assertSame(
            self::$manifest->params()["...variadic_int"],
            $autowiring->variadic_int,
            "variadic params must be inject",
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testOptionalArgument(): void
    {
        $obj = $this->container->get(EntrypointOptionalArgument::class);
        Assert::assertInstanceOf(EntrypointOptionalArgument::class, $obj);
        Assert::assertInstanceOf(I::class, $obj->i);
        Assert::assertSame("default value", $obj->string_default);
        Assert::assertInstanceOf(SWithParam::class, $obj->s);
        Assert::assertSame("default param", $obj->s->name);
        Assert::assertSame("other value", $obj->must_continue_not_break);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAuthShareDependencyBetweenEntrypoint(): void
    {
        /** @var EntrypointSharedDependency $entrypoint_shared */
        $entrypoint_shared = $this->container->get(
            EntrypointSharedDependency::class,
        );
        Assert::assertInstanceOf(
            EntrypointSharedDependency::class,
            $entrypoint_shared,
        );
        /** @var EntrypointAutowiring $autowiring */
        $autowiring = $this->container->get(EntrypointAutowiring::class);
        Assert::assertSame(
            $entrypoint_shared->s,
            $autowiring->s,
            "dependency between few entrypoint must be auto shared (same)",
        );
        Assert::assertSame(
            self::$manifest->params()[
                EntrypointSharedDependency::class . '$argument_to_custom_param'
            ],
            $entrypoint_shared->argument_to_custom_param,
            "must be set custom param only for this class",
        );
        Assert::assertSame(
            self::$manifest->params()["argument_to_custom_alias_custom_value"],
            $entrypoint_shared->argument_to_custom_alias,
            "must be used custom alias only for this class",
        );
        Assert::assertSame(
            self::$manifest->params()["argument_to_custom_alias_custom_value"],
            $entrypoint_shared->argument_to_custom_alias2,
            "after alias must be correct detect param with array_pop stack",
        );
        Assert::assertSame(
            self::$manifest->params()[
                "..." . EntrypointSharedDependency::class . '$variadic_int'
            ],
            $entrypoint_shared->variadic_int,
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
        Assert::assertInstanceOf(stdClass::class, $obj);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAutowiringVariadicClass(): void
    {
        /** @var EntrypointVariadicClass $obj */
        $obj = $this->container->get(EntrypointVariadicClass::class);
        Assert::assertSame(
            self::$manifest->params()["..." . A::class],
            $obj->a_array,
            "variadic params without primitive must be correct injected",
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testOverwrittenExtendConstructor(): void
    {
        /** @var EntrypointOverwriteExtendConstructor $obj */
        $obj = $this->container->get(
            EntrypointOverwriteExtendConstructor::class,
        );
        Assert::assertSame(
            self::$manifest->params()[
                EntrypointOverwriteExtendConstructor::class . '$username'
            ],
            $obj->username,
            "value from base constructor must be overwritten by custom rule ",
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
                "Entries: %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s must be declared in params",
                "username",
                "password",
                S::class . "|string",
                "dsn",
                "argument_to_custom_param",
                "argument_to_custom_alias_value",
                EntrypointSharedDependency::class . '$argument_to_custom_param',
                "argument_to_custom_alias_custom_value",
                "..." . EntrypointSharedDependency::class . '$variadic_int',
                "...variadic_int",
                "..." . A::class,
                EntrypointOverwriteExtendConstructor::class . '$username',
            ),
        );
        new (self::$manifest->fqcn()->fqcn)([]);
    }

    public function testInfiniteRecursion(): void
    {
        $this->expectException(\Cekta\DI\Exception\CircularDependency::class);
        $this->expectExceptionMessage(
            sprintf(
                "`%s` has circular dependency, stack: %s, %s",
                EntrypointCircularDependency::class,
                EntrypointCircularDependency::class,
                CircularDependency::class,
            ),
        );
        $manifest = new Manifest(
            filename: self::$manifest->filename(),
            fqcn: self::$manifest->fqcn()->fqcn,
            params: [],
            definition: [
                'entries' => [EntrypointCircularDependency::class],
            ],
        );
        $compiler = new ContainerGenerator();
        $compiler->generate($manifest);
    }
}
