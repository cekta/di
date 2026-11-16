<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\DependencyMap\Dependency\Alias;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\DependencyMap\Dependency\Param;

/**
 * @external
 * @deprecated will be removed on next release, use ContainerCompiler
 */
readonly class ContainerBuilder
{
    public FQCN $fqcn;

    private readonly Manifest $manifest;
    /**
     * @param array<string> $entries
     * @param array<string, mixed|Lazy> $params
     * @param array<string, string> $alias
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        public array $entries = [],
        public array $params = [],
        public array $alias = [],
        string $fqcn = 'App\Container',
        public array $singletons = [],
        public array $factories = [],
    ) {
        $this->manifest = new Manifest('tmp stub', $fqcn, $params, [
            'entries' => $entries,
            'alias' => $alias,
            'singletons' => $singletons,
            'factories' => $factories,
        ]);
        $this->fqcn = $this->manifest->fqcn();
    }

    public function build(): string
    {
        return new ContainerGenerator()->generate($this->manifest);
    }
}
