<?php

declare(strict_types=1);

namespace Cekta\DI;

/**
 * @internal not for public usage
 */
class Dependency
{
    public function __construct(
        private string $name,
        private bool $variadic = false
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isVariadic(): bool
    {
        return $this->variadic;
    }
}
