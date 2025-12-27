<?php

declare(strict_types=1);

namespace Cekta\DI\DependencyMap;

final readonly class Parameter
{
    public function __construct(
        public string $name,
        public string $argument_name,
        public bool $is_optional,
        public bool $is_variadic,
    ) {
    }
}
