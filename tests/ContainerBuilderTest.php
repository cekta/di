<?php

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\ContainerBuilder;
use Cekta\DI\Test\Fixture\A;
use Cekta\DI\Test\Fixture\B;
use Cekta\DI\Test\Fixture\ExampleParams;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerBuilderTest extends TestCase
{
    public ContainerBuilder $builder;

    public function testBuildDefault(): void
    {
        $container = $this->builder->build();
        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(A::class, $container->get(A::class));
        $this->assertInstanceOf(B::class, $container->get(A::class)->b);
    }

    protected function setUp(): void
    {
        $this->builder = new ContainerBuilder();
    }

    public function testBuildParams(): void
    {
        $this->builder->params([
            'a' => 123,
            'b' => 321,
        ]);
        $container = $this->builder->build();
        $this->assertSame(123, $container->get('a'));
        $this->assertSame(321, $container->get('b'));
        $this->assertInstanceOf(ExampleParams::class, $container->get(ExampleParams::class));
    }

    public function testBuildAlias(): void
    {
        $this->builder->params([
            'a' => 'param a',
        ]);
        $this->builder->alias([
            'example' => 'a',
        ]);
        $container = $this->builder->build();
        $this->assertSame('param a', $container->get('example'));
    }

    public function testDefinition(): void
    {
        $this->builder->params([
            'a' => 'value a',
        ]);
        $this->builder->definitions([
            'example' => function (ContainerInterface $container) {
                return [$container->get('a')];
            },
        ]);
        $result = $this->builder->build()->get('example');
        $this->assertIsArray($result);
        $this->assertSame('value a', $result[0]);
    }
}
