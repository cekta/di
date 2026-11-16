<?php

declare(strict_types=1);

namespace Cekta\DI;

readonly class Definition
{
    /**
     * @param array<string> $entries
     * @param array<string, string> $alias
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        public array $entries = [],
        public array $alias = [],
        public array $singletons = [],
        public array $factories = [],
    ) {
    }
}
