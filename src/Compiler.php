<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\CircularDependency;
use Cekta\DI\Exception\NotInstantiable;

/**
 * @external
 */
readonly class Compiler
{
    public FQCN $fqcn;
    /**
     * @param array<string> $containers
     * @param array<string, mixed|Lazy> $params
     * @param array<string, string> $alias
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        public array $containers = [],
        public array $params = [],
        public array $alias = [],
        string $fqcn = 'App\Container',
        public array $singletons = [],
        public array $factories = [],
    ) {
        $this->fqcn = new FQCN($fqcn);
    }

    /**
     * @return string
     */
    public function compile(): string
    {
        $internal_compiler = new InternalCompiler();
        return $internal_compiler->generate($this);
    }
}
