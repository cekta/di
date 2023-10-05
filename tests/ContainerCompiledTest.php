<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Test\Fixture\Builder;
use Cekta\DI\Test\Fixture\ExampleWithoutConstructor;
use Cekta\DI\Test\Fixture\I;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

class ContainerCompiledTest extends ContainerTest
{
    protected function setUp(): void
    {
        if (!isset($this->container)) {
            $builder = new Builder();
            $compiled = $builder->compile();
            file_put_contents(__DIR__ . '/ExampleCompiled.php', $compiled);
            $container = $builder->build();
            /** @psalm-var  class-string<object> $expected */
            $expected = 'Cekta\\DI\\Test\\ExampleCompiled';
            $this->assertInstanceOf($expected, $container);
            $this->container = $container;
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(__DIR__ . '/ExampleCompiled.php')) {
            unlink(__DIR__ . '/ExampleCompiled.php');
        }
        if (file_exists(__DIR__ . '/TestReflectionEnabled.php')) {
            unlink(__DIR__ . '/TestReflectionEnabled.php');
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testOverwriteBuildAlias(): void
    {
        $builder = new ContainerBuilder();
        $builder->fqcn(Builder::$FQCN)
            ->alias([I::class => 'username'])
            ->params(['username' => 'root']);
        $container = $builder->build();
        $this->assertSame('root', $container->get(I::class));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReflectionEnabled(): void
    {
        /** @psalm-var  class-string<object> $fqcn */
        $fqcn = 'Cekta\\DI\\Test\\TestReflectionEnabled';
        $filename = __DIR__ . '/TestReflectionEnabled.php';
        $builder = new ContainerBuilder();
        file_put_contents(
            $filename,
            $builder->fqcn($fqcn)
                ->compile([], reflection_enabled: true)
        );
        $container = $builder->build();
        unlink($filename);
        $this->assertInstanceOf($fqcn, $container);
        $this->assertInstanceOf(ExampleWithoutConstructor::class, $container->get(ExampleWithoutConstructor::class));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testReflectionDisabledByDefault(): void
    {
        $this->expectException(NotFound::class);
        $this->container->get(stdClass::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAliasOverridableFalse(): void
    {
        /** @psalm-var  class-string<object> $fqcn */
        $fqcn = 'Cekta\\DI\\Test\\TestAliasOverridableFalse';
        $filename = __DIR__ . '/TestAliasOverridableFalse.php';
        $builder = new ContainerBuilder();
        file_put_contents(
            $filename,
            $builder->fqcn($fqcn)
                ->alias(['test' => 'value1'])
                ->params(['value1' => 'value1'])
                ->compile([], alias_overridable:  false)
        );
        $builder->alias(['test' => 'value2']);
        $container = $builder->build();
        unlink($filename);
        $this->assertInstanceOf($fqcn, $container);
        $this->assertSame('value1', $container->get('test'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDefinitionOverridableFalse(): void
    {
        /** @psalm-var  class-string<object> $fqcn */
        $fqcn = 'Cekta\\DI\\Test\\TestDefinitionOverridableFalse';
        $filename = __DIR__ . '/TestDefinitionOverridableFalse.php';
        $builder = new ContainerBuilder();
        file_put_contents(
            $filename,
            $builder->fqcn($fqcn)
                ->alias(['test' => 'value1'])
                ->params(['value1' => 'value1'])
                ->compile([], definition_overridable:  false)
        );
        $builder->definitions(['test' => function () {
            return 'value2';
        }]);
        $container = $builder->build();
        unlink($filename);
        $this->assertInstanceOf($fqcn, $container);
        $this->assertSame('value1', $container->get('test'));
    }
}
