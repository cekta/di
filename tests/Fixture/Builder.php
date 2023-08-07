<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

use Cekta\DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class Builder
{
    public static array $PARAMS;
    public static array $ALIAS = [
        I::class => R::class,
        ExampleOverwrite::class . '$username' => 'overwrite_username',
    ];
    private ContainerBuilder $builder;
    /**
     * @psalm-var  class-string<object>
     */
    public static string $FQCN = 'Cekta\\DI\\Test\\ExampleCompiled';

    public function __construct()
    {
        $this::$PARAMS = [
            'username' => 'username_value',
            'password' => 'password_value',
            'db_type' => 'mysql',
            'db_name' => 'test',
            'db_host' => '127.0.0.1',
            'overwrite_username' => 'other_username',
            A::class . '|int' => 54321,
            '...variadic_params' => [123, 456],
            '...variadic_strings' => ['hello', 'world'],
            'null' => null,
        ];
        self::$PARAMS[sprintf('(%s&%s)|int', A::class, B::class)] = 12345;
        self::$PARAMS[sprintf('...(%s&%s)|int', A::class, B::class)] = [456, 321];
        self::$PARAMS[sprintf('...%s|int', A::class)] = [543, 21];
        self::$PARAMS[sprintf('...%s', ExampleWithoutConstructor::class)] = [
            new ExampleWithoutConstructor(),
            new ExampleWithoutConstructor()
        ];
        self::$ALIAS[sprintf(
            '...%s$variadic_primitive_params',
            ExampleVariadicOverwrite::class
        )] = 'variadic_overwrite_param';
        self::$PARAMS['variadic_overwrite_param'] = [
            'overwrite'
        ];
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            self::$PARAMS[
                sprintf('%s&%s', A::class, I::class)
            ] = new C('', '', '');
            self::$PARAMS[
                sprintf('...%s&%s', A::class, I::class)
            ] = [new C('', '', '')];
        }

        $this->builder = new ContainerBuilder();

        $this->builder->params(self::$PARAMS)
            ->alias(self::$ALIAS)
            ->definitions([
                'dsn' => function (ContainerInterface $c) {
                    $db_type = $c->get('db_type');
                    $db_name = $c->get('db_name');
                    $db_host = $c->get('db_host');
                    assert(is_string($db_type) && is_string($db_name) && is_string($db_host));
                    return "$db_type:dbname=$db_name;host=$db_host";
                },
            ]);
    }

    public function compile(): string|false
    {
        $this->builder->fqcn(static::$FQCN);
        $containers = [
            ExampleNamed::class,
            ExampleWithoutType::class,
            ExampleMixType::class,
            ExampleWithoutConstructor::class,
            ExampleOverwrite::class,
            ExampleUnionType::class,
            ExampleVariadicWithoutType::class,
            ExampleVariadicPrimitive::class,
            ExampleVariadicUnion::class,
            ExampleVariadicNamedType::class,
            ExampleVariadicOverwrite::class,
        ];
        if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
            $containers[] = ExampleDNFType::class;
            $containers[] = ExampleVariadicDNFType::class;
        }
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $containers[] = ExampleIntersectionType::class;
            $containers[] = ExampleVariadicIntersection::class;
        }
        return $this->builder->compile($containers);
    }

    public function build(): ContainerInterface
    {
        return $this->builder->build();
    }
}
