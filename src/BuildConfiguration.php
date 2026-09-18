<?php

declare(strict_types=1);

namespace Cekta\DI;

readonly class BuildConfiguration
{
    /**
     * @param array<string> $entries
     * @param array<string, mixed|Lazy> $params
     * @param array<string, string> $alias
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        public FQCN $fqcn,
        public array $entries = [],
        public array $params = [],
        public array $alias = [],
        public array $singletons = [],
        public array $factories = [],
    ) {
    }
}
