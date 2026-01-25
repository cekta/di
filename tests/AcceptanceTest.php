<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Compiler;
use Cekta\DI\Exception\CircularDependency as CircularDependencyException;
use Cekta\DI\Exception\IntersectConfiguration;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Module;
use Cekta\DI\Project;
use Cekta\DI\Test\AcceptanceTest\A;
use Cekta\DI\Test\AcceptanceTest\CircularDependency;
use Cekta\DI\Test\AcceptanceTest\ContainerCreatedWithNew;
use Cekta\DI\Test\AcceptanceTest\Discovery\Entrypoint;
use Cekta\DI\Test\AcceptanceTest\Discovery\EntrypointExample;
use Cekta\DI\Test\AcceptanceTest\Discovery\ProjectModule;
use Cekta\DI\Test\AcceptanceTest\Discovery\ProjectSecondModule;
use Cekta\DI\Test\AcceptanceTest\EntrypointAutowiring;
use Cekta\DI\Test\AcceptanceTest\EntrypointCircularDependency;
use Cekta\DI\Test\AcceptanceTest\EntrypointOptionalArgument;
use Cekta\DI\Test\AcceptanceTest\EntrypointOverwriteExtendConstructor;
use Cekta\DI\Test\AcceptanceTest\EntrypointSharedDependency;
use Cekta\DI\Test\AcceptanceTest\EntrypointVariadicClass;
use Cekta\DI\Test\AcceptanceTest\I;
use Cekta\DI\Test\AcceptanceTest\ProjectAppModule;
use Cekta\DI\Test\AcceptanceTest\R1;
use Cekta\DI\Test\AcceptanceTest\S;
use Cekta\DI\Test\AcceptanceTest\SWithParam;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use stdClass;

class AcceptanceTest extends TestCase
{
    protected ContainerInterface $container;
    protected Project $project;

    private ProjectAppModule $app;
    private string $container_fqcn = 'Cekta\DI\Test\AcceptanceTest\AppContainer';

    protected function setUp(): void
    {
        $this->app = new ProjectAppModule();
        $this->project = new Project(
            modules: [$this->app],
            container_filename: __DIR__ . '/AcceptanceTest/AppContainer.php',
            container_fqcn: $this->container_fqcn,
            discover_filename: __DIR__ . '/AcceptanceTest/discover.php',
        );
        $this->container = $this->project->container();
    }

    public static function tearDownAfterClass(): void
    {
        $reflection = new ReflectionClass(self::class);
        foreach ($reflection->getMethods() as $method) {
            foreach (
                [
                    __DIR__ . '/AcceptanceTest/' . $method->name . '.php',
                    __DIR__ . '/AcceptanceTest/' . ucfirst($method->name) . 'Container.php',
                ] as $filename
            ) {
                if (file_exists($filename)) {
                    unlink($filename);
                }
            }
        }

        foreach (
            [
                __DIR__ . '/AcceptanceTest/discover.php',
                __DIR__ . '/AcceptanceTest/AppContainer.php'
            ] as $filename
        ) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAllEntriesMustBeAvailableAndGettable(): void
    {
        foreach ($this->app->entries as $key) {
            Assert::assertTrue($this->container->has($key), 'available for get');
            Assert::assertNotEmpty($this->container->get($key), 'all entries must be gettable');
        }
        Assert::assertFalse($this->container->has('invalid name'));
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
        Assert::assertInstanceOf(EntrypointAutowiring::class, $autowiring);
        Assert::assertSame(
            $this->app->container_params['username'],
            $autowiring->username,
            'string(primitive) params must be inject'
        );
        Assert::assertSame(
            $this->app->container_params['password'],
            $autowiring->password,
            'string(primitive) params must be inject'
        );

        Assert::assertInstanceOf(
            ContainerCreatedWithNew::class,
            $autowiring->created_with_new,
            'autowiring dependency must be correct'
        );
        Assert::assertInstanceOf(
            R1::class,
            $autowiring->i,
            'alias for interface must be correct resolved'
        );
        Assert::assertInstanceOf(
            S::class,
            $autowiring->s,
            'autowiring first dependency',
        );
        Assert::assertSame(
            $autowiring->s,
            $autowiring->s2,
            'must be auto shared between one entrypoint'
        );
        Assert::assertSame(
            $autowiring->s,
            $autowiring->s3,
            'must be called array_pop on shared',
        );
        Assert::assertSame(
            $autowiring->s,
            $autowiring->s4,
            'must be called array_pop on everytime',
        );
        Assert::assertSame(
            $this->app->container_params[S::class . '|string'],
            $autowiring->union_type,
            'union|dfn params must work'
        );
        Assert::assertSame(
            'definition u: some username, p: some password',
            $autowiring->dsn,
            'lazy loading params must be correct inject'
        );
        Assert::assertSame(
            $this->app->container_params['argument_to_custom_param'],
            $autowiring->argument_to_custom_param,
            'must default value from param, no custom param'
        );
        Assert::assertSame(
            $this->app->container_params['argument_to_custom_alias_value'],
            $autowiring->argument_to_custom_alias,
            'must default alias, no custom alias'
        );
        Assert::assertInstanceOf(
            EntrypointSharedDependency::class,
            $autowiring->exampleShared,
            'other entrypoint must be correct inject'
        );
        Assert::assertSame(
            $this->app->container_params['...variadic_int'],
            $autowiring->variadic_int,
            'variadic params must be inject'
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
        Assert::assertSame('default value', $obj->string_default);
        Assert::assertInstanceOf(SWithParam::class, $obj->s);
        Assert::assertSame('default param', $obj->s->name);
        Assert::assertSame('other value', $obj->must_continue_not_break);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testOptionalArgumentOverwrite(): void
    {
        $params = [
            'string_default' => 'overwritten value',
        ];
        $compiler = new Compiler(
            containers: [
                EntrypointOptionalArgument::class,
            ],
            params: $params,
            alias: [
                I::class => R1::class,
            ],
            fqcn: 'Cekta\DI\Test\AcceptanceTest\ContainerOptionalArgumentOverwrite',
        );
        $filename = __DIR__ . '/AcceptanceTest/ContainerOptionalArgumentOverwrite.php';
        file_put_contents($filename, $compiler->compile());
        /**
         * @var ContainerInterface $container
         * @phpstan-ignore class.notFound
         */
        $container = new ('Cekta\DI\Test\AcceptanceTest\ContainerOptionalArgumentOverwrite')($params);
        $obj = $container->get(EntrypointOptionalArgument::class);
        Assert::assertInstanceOf(EntrypointOptionalArgument::class, $obj);
        Assert::assertSame('other value', $obj->must_continue_not_break);
        unlink($filename);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAuthShareDependencyBetweenEntrypoint(): void
    {
        /** @var EntrypointSharedDependency $entrypoint_shared */
        $entrypoint_shared = $this->container->get(EntrypointSharedDependency::class);
        Assert::assertInstanceOf(EntrypointSharedDependency::class, $entrypoint_shared);
        /** @var EntrypointAutowiring $autowiring */
        $autowiring = $this->container->get(EntrypointAutowiring::class);
        Assert::assertSame(
            $entrypoint_shared->s,
            $autowiring->s,
            'dependency between few entrypoint must be auto shared (same)'
        );
        Assert::assertSame(
            $this->app->container_params[EntrypointSharedDependency::class . '$argument_to_custom_param'],
            $entrypoint_shared->argument_to_custom_param,
            'must be set custom param only for this class'
        );
        Assert::assertSame(
            $this->app->container_params['argument_to_custom_alias_custom_value'],
            $entrypoint_shared->argument_to_custom_alias,
            'must be used custom alias only for this class'
        );
        Assert::assertSame(
            $this->app->container_params['argument_to_custom_alias_custom_value'],
            $entrypoint_shared->argument_to_custom_alias2,
            'after alias must be correct detect param with array_pop stack'
        );
        Assert::assertSame(
            $this->app->container_params['...' . EntrypointSharedDependency::class . '$variadic_int'],
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
            $this->app->container_params['...' . A::class],
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
        Assert::assertSame(
            $this->app->container_params[EntrypointOverwriteExtendConstructor::class . '$username'],
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
                'Entries: %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s must be declared in params',
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
        new ($this->container_fqcn)([]);
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

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function testDiscovery(): void
    {
        $project = new Project(
            [
                new ProjectModule(),
                new ProjectSecondModule(),
            ],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
            function () {
                $classes = [EntrypointExample::class, Entrypoint::class, stdClass::class];
                $result = [];
                foreach ($classes as $class) {
                    $result[] = new ReflectionClass($class);
                }
                return $result;
            }
        );
        $project->clean();
        // generate discovery
        $container = $project->container();
        Assert::assertInstanceOf(EntrypointExample::class, $container->get(EntrypointExample::class));
        Assert::assertSame([EntrypointExample::class], $container->get('for_test'));
        Assert::assertSame(ProjectSecondModule::SECOND_TEST, $container->get('second_test'));
        // usage generated discovery
        $container = $project->container();
        Assert::assertInstanceOf(EntrypointExample::class, $container->get(EntrypointExample::class));
        Assert::assertSame([EntrypointExample::class], $container->get('for_test'));
        Assert::assertSame(ProjectSecondModule::SECOND_TEST, $container->get('second_test'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testEmptyDiscoverFileMustBeUpdate(): void
    {
        $data = [];
        file_put_contents(
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
            '<?php return ' . var_export($data, true) . ';'
        );
        $project = new Project(
            [new ProjectSecondModule()],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php'
        );
        $container = $project->container();

        Assert::assertSame(ProjectSecondModule::SECOND_TEST, $container->get('second_test'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDiscoverFileReturnNotArray(): void
    {
        $data = 'not array value';
        file_put_contents(
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
            '<?php return ' . var_export($data, true) . ';'
        );
        $project = new Project(
            [new ProjectSecondModule()],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php'
        );
        $container = $project->container();

        Assert::assertSame(ProjectSecondModule::SECOND_TEST, $container->get('second_test'));
    }

    public function testDiscoverGeneratedOnce(): void
    {
        $module = $this->createMock(Module::class);
        $module->expects($this->once())
            ->method('getEncodedModule')
            ->willReturn('');
        $project = new Project(
            [$module],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php'
        );
        $project->container(); // discover must be generated
        $project->container(); // discover must be from file
    }


    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDiscoveryWithOldDataMustBeUpdate(): void
    {
        $data = [
            'modules' => ['test from module1', 'test from module2'],
        ];
        file_put_contents(
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
            '<?php return ' . var_export($data, true) . ';'
        );

        $project = new Project(
            [new ProjectSecondModule()],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
        );
        $container = $project->container();

        Assert::assertSame(ProjectSecondModule::SECOND_TEST, $container->get('second_test'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDiscoveryModulesNotArray(): void
    {
        $data = [
            'modules' => 'not array value',
        ];
        file_put_contents(
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
            '<?php return ' . var_export($data, true) . ';'
        );

        $project = new Project(
            [new ProjectSecondModule()],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
        );
        $container = $project->container();

        Assert::assertSame(ProjectSecondModule::SECOND_TEST, $container->get('second_test'));
    }

    public function testCleanExist(): void
    {
        touch(__DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php');
        touch(__DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php');
        $project = new Project(
            [$this->createMock(Module::class)],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
        );
        $project->clean();
        Assert::assertFalse(file_exists(__DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php'));
        Assert::assertFalse(file_exists(__DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php'));
    }

    public function testCleanNotExist(): void
    {
        $discover_filename = __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php';
        $container_filename = __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php';
        Assert::assertFalse(file_exists($discover_filename));
        Assert::assertFalse(file_exists($container_filename));
        $project = new Project(
            [$this->createMock(Module::class)],
            $container_filename,
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            $discover_filename,
        );
        $project->clean();
        Assert::assertFalse(file_exists($discover_filename));
        Assert::assertFalse(file_exists($container_filename));
    }

    public function testCreateProjectWithoutModules(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`modules` must be not empty');
        new Project(
            [],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php',
        );
    }

    public function testCreateContainerIntersect(): void
    {
        $project = new Project(
            [
                new AcceptanceTest\DiscoveryIntersection\Module([], []),
                new AcceptanceTest\DiscoveryIntersection\Module([], []),
            ],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php'
        );
        $project->container();

        $project = new Project(
            [
                new AcceptanceTest\DiscoveryIntersection\Module([], [
                    'test_intersection' => '1',
                ]),
                new AcceptanceTest\DiscoveryIntersection\Module([], [
                    'test_intersection' => '1',
                ]),
            ],
            __DIR__ . '/AcceptanceTest/' . ucfirst(__FUNCTION__) . 'Container.php',
            'Cekta\DI\Test\AcceptanceTest\\' . ucfirst(__FUNCTION__) . 'Container',
            __DIR__ . '/AcceptanceTest/' . __FUNCTION__ . '.php'
        );
        $this->expectException(IntersectConfiguration::class);
        $this->expectExceptionMessage('Intersect params, for keys: test_intersection');
        $project->container();
    }
}
