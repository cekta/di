<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionParameter;

/**
 * @internal not for public usage
 */
readonly class Dependency
{
    public function __construct(
        public string $name,
        public ReflectionParameter $parameter,
    ) {
    }
}
