<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use RuntimeException;

class NotInstantiable extends RuntimeException
{
    /**
     * @param string $container
     * @param array<string> $stack
     */
    public function __construct(string $container, array $stack)
    {
        parent::__construct(
            sprintf(
                "`$container` must be instantiable, stack: %s",
                implode(', ', $stack)
            )
        );
    }
}
