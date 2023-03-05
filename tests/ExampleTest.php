<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use Cekta\DI\Test\Fixture\A;
use Cekta\DI\Test\Fixture\B;
use Cekta\DI\Test\Fixture\C;
use Cekta\DI\Test\Fixture\Example;
use Cekta\DI\Test\Fixture\Example2;
use Cekta\DI\Test\Fixture\Example3;
use Cekta\DI\Test\Fixture\Example4;
use Cekta\DI\Test\Fixture\Example5;
use Cekta\DI\Test\Fixture\Example6;
use Cekta\DI\Test\Fixture\Example7;
use Cekta\DI\Test\Fixture\Example8;
use Cekta\DI\Test\Fixture\I;
use Cekta\DI\Test\Fixture\R;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class ExampleTest extends TestCase
{
    private static array $PARAMS;
    private static array $ALIAS;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDynamicContainer(): ContainerBuilder
    {
        $this::$ALIAS = [
            I::class => R::class,
        ];
        $this::$PARAMS = [
            'username' => 'username_value',
            'password' => 'password_value',
            'db_type' => 'mysql',
            'db_name' => 'test',
            'db_host' => '127.0.0.1',
            Example5::class . '$username' => 'other_username',
            A::class . '|int' => 54321,
        ];
        self::$PARAMS[sprintf('(%s&%s)|int', A::class, B::class)] = 12345;
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            self::$PARAMS[sprintf('%s&%s', A::class, I::class)] = new C('', '', '');
        }
        $builder = (new ContainerBuilder())
            ->params(self::$PARAMS)
            ->alias(self::$ALIAS)
            ->definitions([
                'dsn' => function (ContainerInterface $c) {
                    return "{$c->get('db_type')}:dbname={$c->get('db_name')};host={$c->get('db_host')}";
                },
            ]);
        $this->scenario($builder->build());
        return $builder;
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @depends testDynamicContainer
     */
    public function testCompiledContainer(ContainerBuilder $builder): void
    {
        /** @psalm-var  class-string<object> $fqcn */
        $fqcn = 'Cekta\\DI\\Test\\ExampleCompiled';
        $builder->fqcn($fqcn);
        $containers = [
            Example::class,
            Example2::class,
            Example3::class,
            Example4::class,
            Example5::class,
            Example7::class,
        ];
        if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
            $containers[] = Example6::class;
        }
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $containers[] = Example8::class;
        }
        $compiled = $builder->compile($containers);
        $this->assertIsString($compiled);
        file_put_contents(__DIR__ . '/ExampleCompiled.php', $compiled);
        $container = $builder->build();
        $this->assertInstanceOf($fqcn, $container);
        $this->assertStringContainsString('namespace Cekta\\DI\\Test', $compiled);
        $this->scenario($container);
    }

    /**
     * @param ContainerInterface $container
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function scenario(ContainerInterface $container): void
    {
        $this->assertTrue($container->has(Example::class));
        $example = $container->get(Example::class);
        $this->assertTrue($container->has(Example::class));

        $this->assertInstanceOf(Example::class, $example);
        $this->assertSame('mysql:dbname=test;host=127.0.0.1', $example->a->dsn);
        $this->assertSame(self::$PARAMS['username'], $example->a->username);
        $this->assertSame(self::$PARAMS['password'], $example->a->password);
        $this->assertInstanceOf(self::$ALIAS[I::class], $example->i);

        $example_other = $container->get(Example::class);
        $this->assertSame($example, $example_other);
        $this->assertSame($example->a, $example_other->a);

        $example2 = $container->get(Example2::class);
        $this->assertInstanceOf(Example2::class, $example2);
        $this->assertSame(self::$PARAMS['username'], $example2->username);

        $example3 = $container->get(Example3::class);
        $this->assertInstanceOf(Example3::class, $example3);
        $this->assertSame(self::$PARAMS['username'], $example3->b->username);

        $example4 = $container->get(Example4::class);
        $this->assertInstanceOf(Example4::class, $example4);

        $example5 = $container->get(Example5::class);
        $this->assertInstanceOf(Example5::class, $example5);
        $this->assertSame(self::$PARAMS[Example5::class . '$username'], $example5->username);

        if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
            $example6 = $container->get(Example6::class);
            $this->assertInstanceOf(Example6::class, $example6);
            $this->assertSame(self::$PARAMS[sprintf('(%s&%s)|int', A::class, B::class)], $example6->param);
        }

        $example7 = $container->get(Example7::class);
        $this->assertInstanceOf(Example7::class, $example7);
        $this->assertSame(self::$PARAMS[A::class . '|int'], $example7->param);

        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $example8 = $container->get(Example8::class);
            $this->assertInstanceOf(Example8::class, $example8);
            $this->assertSame(self::$PARAMS[sprintf('%s&%s', A::class, I::class)], $example8->param);
        }
    }

    public static function tearDownAfterClass(): void
    {
        unlink(__DIR__ . '/ExampleCompiled.php');
    }
}
