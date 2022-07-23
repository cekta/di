<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use Cekta\DI\Test\Fixture\Example;
use Cekta\DI\Test\Fixture\Example2;
use Cekta\DI\Test\Fixture\Example3;
use Cekta\DI\Test\Fixture\Example4;
use Cekta\DI\Test\Fixture\I;
use Cekta\DI\Test\Fixture\R;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use UnexpectedValueException;

class ExampleTest extends TestCase
{
    public const PARAMS = [
        'username' => 'username_value',
        'password' => 'password_value',
        'db_type' => 'mysql',
        'db_name' => 'test',
        'db_host' => '127.0.0.1',
    ];
    public const ALIAS = [
        I::class => R::class,
    ];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDynamicContainer(): ContainerBuilder
    {
        $builder = (new ContainerBuilder())
            ->params(self::PARAMS)
            ->alias(self::ALIAS)
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
        $compiled = $builder->compile([
            Example::class,
            Example2::class,
            Example3::class,
            Example4::class,
        ]);
        $this->assertIsString($compiled);
        file_put_contents(__DIR__ . '/ExampleCompiled.php', $compiled);
        $container = $builder->build();
        $this->assertInstanceOf($fqcn, $container);
        $this->assertStringContainsString('namespace Cekta\\DI\\Test', $compiled);
        $this->scenario($container);
    }

    public function testCompilationFail(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('`invalid container` is cant be resolved');
        (new ContainerBuilder())
            ->compile(['invalid container']);
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws ReflectionException
     * @depends testDynamicContainer
     */
    public function testCompilationWithoutNamespace(ContainerBuilder $builder): void
    {
        $builder->fqcn('\\Container');
        $compiled = $builder->compile([Example::class]);
        $this->assertIsString($compiled);
        $this->assertStringNotContainsString('namespace', $compiled);
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws ReflectionException
     * @depends testDynamicContainer
     */
    public function testCompilationInvalidNamespace(ContainerBuilder $builder): void
    {
        $fqcn = 'Container';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid fqcn: `{$fqcn}` must contain \\");
        $builder->fqcn($fqcn);
        $builder->compile([Example::class]);
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
        $this->assertSame(self::PARAMS['username'], $example->a->username);
        $this->assertSame(self::PARAMS['password'], $example->a->password);
        $this->assertInstanceOf(self::ALIAS[I::class], $example->i);

        $example_other = $container->get(Example::class);
        $this->assertSame($example, $example_other);
        $this->assertSame($example->a, $example_other->a);

        $example3 = $container->get(Example3::class);
        $this->assertInstanceOf(Example3::class, $example3);
        $this->assertSame(self::PARAMS['username'], $example3->b->username);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(__DIR__ . '/ExampleCompiled.php');
    }
}
