<?php

declare(strict_types=1);

namespace Cekta\DI\Test\ContainerBuilderTest;

use Cekta\DI\Lazy\Closure;
use Psr\Container\ContainerInterface;
use stdClass;

readonly class App
{
    /**
     * @var array<string, mixed>
     */
    public array $params;
    public function __construct(
        public array $entries = [
            stdClass::class,
            EntrypointAutowiring::class,
            EntrypointSharedDependency::class,
            EntrypointVariadicClass::class,
            EntrypointOverwriteExtendConstructor::class,
            EntrypointOptionalArgument::class,
        ],
        public array $alias = [
            I::class => R1::class,
            'argument_to_custom_alias' => 'argument_to_custom_alias_value',
            EntrypointSharedDependency::class . '$argument_to_custom_alias' => 'argument_to_custom_alias_custom_value',
            EntrypointSharedDependency::class . '$argument_to_custom_alias2' => 'argument_to_custom_alias_custom_value',
        ],
    ) {
        $this->params = [
            'username' => 'some username',
            'password' => 'some password',
            EntrypointOverwriteExtendConstructor::class . '$username' => 'base constructor overwritten username',
            'argument_to_custom_param' => 'default param',
            EntrypointSharedDependency::class . '$argument_to_custom_param' => 'custom value param',
            'argument_to_custom_alias_value' => 'default value for alias',
            'argument_to_custom_alias_custom_value' => 'custom value for alias',
            'db_username' => 'some db username',
            S::class . '|string' => 'named params: ' . S::class . '|string',
            '...variadic_int' => [1, 3, 5],
            '...' . EntrypointSharedDependency::class . '$variadic_int' => [9, 8, 7],
            '...' . A::class => [new A(), new A()],
            'dsn' => new Closure(function (ContainerInterface $container) {
                /** @var string $username */
                $username = $container->get('username');
                /** @var string $password */
                $password = $container->get('password');
                return "definition u: $username, p: $password";
            })
        ];
    }
}
