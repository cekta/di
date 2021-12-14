<?php

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\ContainerBuilder;
use Cekta\DI\ContainerCompiledFactory;
use Cekta\DI\ContainerDevelopFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContainerBuilderTest extends TestCase
{
    public function testBuildDefault(): void
    {
        $builder = new ContainerBuilder();
        $result = $builder->build();
        $this->assertInstanceOf(Container::class, $result);
    }

    public function testBuildCheckDefaultArguments(): void
    {
        $builder = new ContainerBuilder();
        $builder->setDevelopFactory(new ContainerDevelopFactory(ContainerDevelop::class));
        $result = $builder->build();
        assert($result instanceof ContainerDevelop);
        $this->assertSame([], $result->params);
        $this->assertSame([], $result->interfaces);
        $this->assertSame([], $result->definitions);
    }

    public function testBuildCheckCustomArguments(): void
    {
        $params = ['param' => 'value'];
        $definitions = [
            'definition' => function () {
            },
            'd2' => function () {
            },
        ];
        $interfaces = [
            'interface' => 'class',
            'i2' => 'c2'
        ];
        $builder = new ContainerBuilder();
        $builder->setDevelopFactory(new ContainerDevelopFactory(ContainerDevelop::class))
            ->setParams($params)
            ->setDefinitionProvider(function () use ($definitions) {
                return $definitions;
            })
            ->setInterfaceProvider(function () use ($interfaces) {
                return $interfaces;
            });
        $result = $builder->build();
        assert($result instanceof ContainerDevelop);
        $this->assertSame($params, $result->params);
        $this->assertSame($interfaces, $result->interfaces);
        $this->assertSame($definitions, $result->definitions);
    }

    public function testBuildCompiled(): void
    {
        $params = ['param' => 'value'];
        $builder = new ContainerBuilder();
        $builder->setCompiledFactory(new ContainerCompiledFactory(ContainerCompiled::class))
            ->setParams($params)
            ->setDefinitionProvider(function () {
                $this->fail();
            })
            ->setInterfaceProvider(function () {
                $this->fail();
            });
        $result = $builder->build();
        assert($result instanceof ContainerCompiled);
        $this->assertSame($params, $result->params);
    }

    public function testDefinitionProviderReturnNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('definition provider must return array');
        $builder = new ContainerBuilder();
        $builder->setDefinitionProvider(function () {
            return 123;
        });
        $builder->build();
    }

    public function testDefinitionProviderReturnKeyNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('definition provider must return array with all keys is string');
        $builder = new ContainerBuilder();
        $builder->setDefinitionProvider(function () {
            return [
                function () {
                }
            ];
        });
        $builder->build();
    }

    public function testDefinitionProviderReturnValueNotCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('definition provider must return array with all values is callable');
        $builder = new ContainerBuilder();
        $builder->setDefinitionProvider(function () {
            return [
                'test' => 'not callable'
            ];
        });
        $builder->build();
    }

    public function testInterfaceProviderReturnNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('interface provider must return array');
        $builder = new ContainerBuilder();
        $builder->setInterfaceProvider(function () {
            return 123;
        });
        $builder->build();
    }

    public function testInterfaceProviderReturnKeyNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('interface provider must return array with all keys is string');
        $builder = new ContainerBuilder();
        $builder->setInterfaceProvider(function () {
            return [
                'key not string'
            ];
        });
        $builder->build();
    }

    public function testInterfaceProviderReturnValueNotCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('interface provider must return array with all values is string');
        $builder = new ContainerBuilder();
        $builder->setInterfaceProvider(function () {
            return [
                'test' => 123
            ];
        });
        $builder->build();
    }
}
