<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use RuntimeException;

class InfiniteRecursion extends RuntimeException
{
    /**
     * @param string $container
     * @param string[] $stack
     */
    public function __construct(string $container, array $stack)
    {
        parent::__construct(
            sprintf(
                "Infinite recursion detected for `$container`, stack: %s",
                implode(', ', $stack)
            )
        );
    }
}
