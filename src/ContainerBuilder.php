<?php

declare(strict_types=1);

namespace Cekta\DI;

readonly class ContainerBuilder
{
    public FQCN $fqcn;
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
        $this->fqcn = new FQCN($fqcn);
    }

    /**
     * @return string
     */
    public function build(): string
    {
        $internal_compiler = new ContainerBuilderInternal();
        return $internal_compiler->generate($this);
    }
}
