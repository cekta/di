<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Container;
use Cekta\DI\Test\Fixture\A;
use Cekta\DI\Test\Fixture\B;
use Cekta\DI\Test\Fixture\Builder;
use Cekta\DI\Test\Fixture\ExampleDNFType;
use Cekta\DI\Test\Fixture\ExampleIntersectionType;
use Cekta\DI\Test\Fixture\ExampleMixType;
use Cekta\DI\Test\Fixture\ExampleNamed;
use Cekta\DI\Test\Fixture\ExampleOverwrite;
use Cekta\DI\Test\Fixture\ExampleUnionType;
use Cekta\DI\Test\Fixture\ExampleVariadicDNFType;
use Cekta\DI\Test\Fixture\ExampleVariadicIntersection;
use Cekta\DI\Test\Fixture\ExampleVariadicNamedType;
use Cekta\DI\Test\Fixture\ExampleVariadicOverwrite;
use Cekta\DI\Test\Fixture\ExampleVariadicPrimitive;
use Cekta\DI\Test\Fixture\ExampleVariadicUnion;
use Cekta\DI\Test\Fixture\ExampleVariadicWithoutType;
use Cekta\DI\Test\Fixture\ExampleWithoutConstructor;
use Cekta\DI\Test\Fixture\ExampleWithoutType;
use Cekta\DI\Test\Fixture\I;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        if (!isset($this->container)) {
            $builder = new Builder();
            $container = $builder->build();
            $this->assertInstanceOf(Container::class, $container);
            $this->container = $container;
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testNamed(): void
    {
        $this->assertTrue($this->container->has(ExampleNamed::class));
        $example = $this->container->get(ExampleNamed::class);
        $this->assertTrue($this->container->has(ExampleNamed::class));

        $this->assertInstanceOf(ExampleNamed::class, $example);
        $this->assertSame('mysql:dbname=test;host=127.0.0.1', $example->a->dsn);
        $this->assertSame(Builder::$PARAMS['username'], $example->a->username);
        $this->assertSame(Builder::$PARAMS['password'], $example->a->password);
        $this->assertInstanceOf(Builder::$ALIAS[I::class], $example->i);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMustBeSingleton(): void
    {
        $example = $this->container->get(ExampleNamed::class);
        $example_other = $this->container->get(ExampleNamed::class);
        $this->assertInstanceOf(ExampleNamed::class, $example_other);
        $this->assertInstanceOf(ExampleNamed::class, $example);
        $this->assertSame($example, $example_other);
        $this->assertSame($example->a, $example_other->a);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWithoutType(): void
    {
        $example2 = $this->container->get(ExampleWithoutType::class);
        $this->assertInstanceOf(ExampleWithoutType::class, $example2);
        $this->assertSame(Builder::$PARAMS['username'], $example2->username);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testWithoutConstructor(): void
    {
        $this->assertInstanceOf(
            ExampleWithoutConstructor::class,
            $this->container->get(ExampleWithoutConstructor::class)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testOverwrite(): void
    {
        $overwrite = $this->container->get(ExampleOverwrite::class);
        $this->assertInstanceOf(ExampleOverwrite::class, $overwrite);
        $this->assertSame(Builder::$PARAMS[ExampleOverwrite::class . '$username'], $overwrite->username);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUnionType(): void
    {
        $union = $this->container->get(ExampleUnionType::class);
        $this->assertInstanceOf(ExampleUnionType::class, $union);
        $this->assertSame(Builder::$PARAMS[A::class . '|int'], $union->param);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testVariadicOverwrite(): void
    {
        $variadic_overwrite = $this->container->get(ExampleVariadicOverwrite::class);
        $this->assertInstanceOf(ExampleVariadicOverwrite::class, $variadic_overwrite);
        $expected = Builder::$PARAMS[sprintf('...%s$variadic_primitive_params', ExampleVariadicOverwrite::class)];
        $this->assertSame($expected, $variadic_overwrite->params);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testVariadicWithoutType(): void
    {
        $variadic_without_type = $this->container->get(ExampleVariadicWithoutType::class);
        $this->assertInstanceOf(ExampleVariadicWithoutType::class, $variadic_without_type);
        $this->assertSame(Builder::$PARAMS['...variadic_params'], $variadic_without_type->params);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testVariadicPrimitive(): void
    {
        $variadic_primitive = $this->container->get(ExampleVariadicPrimitive::class);
        $this->assertInstanceOf(ExampleVariadicPrimitive::class, $variadic_primitive);
        $this->assertSame(Builder::$PARAMS['...variadic_strings'], $variadic_primitive->params);
        $this->assertSame(Builder::$PARAMS['username'], $variadic_primitive->username);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testVariadicUnion(): void
    {
        $variadic_union = $this->container->get(ExampleVariadicUnion::class);
        $this->assertInstanceOf(ExampleVariadicUnion::class, $variadic_union);
        $expected = Builder::$PARAMS[sprintf('...%s|int', A::class)];
        $this->assertSame($expected, $variadic_union->param);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testVariadicNamed(): void
    {
        $variadic_named = $this->container->get(ExampleVariadicNamedType::class);
        $this->assertInstanceOf(ExampleVariadicNamedType::class, $variadic_named);
        $expected = Builder::$PARAMS[sprintf('...%s', ExampleWithoutConstructor::class)];
        $this->assertSame($expected, $variadic_named->param);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDeepDependency(): void
    {
        $this->assertInstanceOf(A::class, $this->container->get(A::class));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMixType(): void
    {
        $example3 = $this->container->get(ExampleMixType::class);
        $this->assertInstanceOf(ExampleMixType::class, $example3);
        $this->assertSame(Builder::$PARAMS['username'], $example3->b->username);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @requires PHP >= 8.1
     */
    public function testIntersection(): void
    {
        $intersection = $this->container->get(ExampleIntersectionType::class);
        $this->assertInstanceOf(ExampleIntersectionType::class, $intersection);
        $this->assertSame(Builder::$PARAMS[sprintf('%s&%s', A::class, I::class)], $intersection->param);

        $variadic_intersection = $this->container->get(ExampleVariadicIntersection::class);
        $this->assertInstanceOf(ExampleVariadicIntersection::class, $variadic_intersection);
        $expected = Builder::$PARAMS[sprintf('...%s&%s', A::class, I::class)];
        $this->assertSame($expected, $variadic_intersection->param);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @requires PHP >= 8.2
     */
    public function testDNFType(): void
    {
        $dnf = $this->container->get(ExampleDNFType::class);
        $this->assertInstanceOf(ExampleDNFType::class, $dnf);
        $this->assertSame(Builder::$PARAMS[sprintf('(%s&%s)|int', A::class, B::class)], $dnf->param);

        $variadic_dnf = $this->container->get(ExampleVariadicDNFType::class);
        $this->assertInstanceOf(ExampleVariadicDNFType::class, $variadic_dnf);
        $expected = Builder::$PARAMS[sprintf('...(%s&%s)|int', A::class, B::class)];
        $this->assertSame($expected, $variadic_dnf->param);
    }
}
