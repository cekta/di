<?php

declare(strict_types=1);

namespace Cekta\DI;

/**
 * @external in v2.x
 * @deprecated use ContainerBuilder in new version.
 */
readonly class Compiler
{
    private ContainerBuilder $container_builder;

    /**
     * @param array<string> $containers
     * @param array<string, mixed|Lazy> $params
     * @param array<string, string> $alias
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        array $containers = [],
        array $params = [],
        array $alias = [],
        string $fqcn = 'App\Container',
        array $singletons = [],
        array $factories = [],
    ) {
        $this->container_builder = new ContainerBuilder(
            entries: $containers,
            params: $params,
            alias: $alias,
            fqcn: $fqcn,
            singletons: $singletons,
            factories: $factories
        );
    }
    public function compile(): string
    {
        return $this->container_builder->build();
    }
}
