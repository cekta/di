<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

use Cekta\DI\LazyClosure;
use Cekta\DI\Module;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use stdClass;

class ProjectAppModule implements Module
{
    /**
     * @var string[]
     */
    public array $entries = [
        stdClass::class,
        EntrypointAutowiring::class,
        EntrypointSharedDependency::class,
        EntrypointVariadicClass::class,
        EntrypointOverwriteExtendConstructor::class,
        EntrypointOptionalArgument::class,
    ];
    /**
     * @var array<string, string>
     */
    public array $alias = [
        I::class => R1::class,
        'argument_to_custom_alias' => 'argument_to_custom_alias_value',
        EntrypointSharedDependency::class . '$argument_to_custom_alias' => 'argument_to_custom_alias_custom_value',
        EntrypointSharedDependency::class . '$argument_to_custom_alias2' => 'argument_to_custom_alias_custom_value',
    ];
    /**
     * @var array<string, mixed>
     */
    public array $container_params;
    /**
     * @inheritdoc
     */
    public function onBuild(string $encoded_module): array
    {
        return [
            'entries' => $this->entries,
            'alias' => $this->alias,
            'factories' => [],
            'singletons' => [],
        ];
    }

    /**
     * @inheritdoc
     */
    public function onCreate(string $encoded_module): array
    {
        if (empty($this->container_params)) {
            $this->container_params = [
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
                'dsn' => new LazyClosure(function (ContainerInterface $container) {
                    /** @var string $username */
                    $username = $container->get('username');
                    /** @var string $password */
                    $password = $container->get('password');
                    return "definition u: $username, p: $password";
                })
            ];
        }
        return $this->container_params;
    }

    /**
     * @inheritdoc
     */
    public function onDiscover(array $classes): string
    {
        return '';
    }
}
