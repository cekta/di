<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use Exception;

class InfiniteRecursion extends Exception
{
    /**
     * @param string $container
     * @param string[] $stack
     */
    public function __construct(string $container, array $stack)
    {
        parent::__construct(
            sprintf("Infinite recursion detected for `{$container}`, stack: %s", implode(', ', $stack))
        );
    }
}
