<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use JetBrains\PhpStorm\Pure;

class IntersectConfiguration extends \RuntimeException
{
    /**
     * @param array<string, mixed> $intersect
     * @param string $key
     */
    public function __construct(array $intersect, string $key)
    {
        $keys = implode(', ', array_keys($intersect));
        parent::__construct("Intersect $key, for keys: $keys");
    }
}
