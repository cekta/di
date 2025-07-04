<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use Exception;

class NotInstantiable extends Exception
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
