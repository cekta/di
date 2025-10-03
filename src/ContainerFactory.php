<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\InvalidContainerForCompile;
use Cekta\DI\Exception\NotInstantiable;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * @external
 * @deprecated use \Cekta\DI\ContainerBuilder
 */
class ContainerFactory
{
    /**
     * @param array<string> $containers
     * @param array<string, mixed> $params
     * @param array<string, string> $alias
     * @param array<string, callable> $definitions
     * @param array<string> $singletons
     * @param array<string> $factories
     *
     * @throws NotInstantiable if container cant be created (interface or abstract class)
     * @throws InfiniteRecursion Invalid compile, infinite recursion in dependencies
     * @throws IOExceptionInterface if file not writable, or other problem with IO
     * @throws InvalidArgumentException if fqcn result not implement ContainerInterface, or cant create Container
     * @throws InvalidContainerForCompile
     */
    public function make(
        string $filename,
        string $fqcn = 'App\Container',
        bool $force_compile = false,
        array $containers = [],
        array $params = [],
        array $alias = [],
        array $definitions = [],
        array $singletons = [],
        array $factories = [],
    ): ContainerInterface {
        return Container::make(
            filename: $filename,
            provider: function () use ($containers, $alias, $singletons, $factories) {
                return [
                    'containers' => $containers,
                    'alias' => $alias,
                    'singletons' => $singletons,
                    'factories' => $factories,
                ];
            },
            params: $params,
            definitions: $definitions,
            fqcn: $fqcn,
            force_compile: $force_compile,
        );
    }
}
