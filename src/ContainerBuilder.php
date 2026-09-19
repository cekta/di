<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\DependencyMap\Dependency\Alias;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\DependencyMap\Dependency\Param;

/**
 * @external
 * @deprecated will be removed on next release, use AbstractProject
 */
readonly class ContainerBuilder
{
    public FQCN $fqcn;
    private readonly AbstractProject $project;
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
        private ContainerCompiler $generator = new ContainerCompiler(),
    ) {
        $this->fqcn = new FQCN($fqcn);
        $this->project = new class ($fqcn, $this) extends AbstractProject {
            public function __construct(
                private string $fqcnString,
                private ContainerBuilder $builder
            ) {
                parent::__construct('tmp stub', $this->fqcnString, $this->builder->params);
            }

            public function definition(): array
            {
                return [
                    'entries' => $this->builder->entries,
                    'alias' => $this->builder->alias,
                    'singletons' => $this->builder->singletons,
                    'factories' => $this->builder->factories,
                ];
            }
        };
    }

    public function build(): string
    {
        return $this->generator->compile($this->project);
    }
}
