<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use RuntimeException;

class CircularDependency extends RuntimeException
{
    /**
     * @param string $container
     * @param string[] $stack
     */
    public function __construct(string $container, array $stack)
    {
        parent::__construct(
            sprintf(
                "`$container` has circular dependency, stack: %s",
                implode(', ', $stack)
            )
        );
    }
}
