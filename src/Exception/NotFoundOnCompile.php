<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use RuntimeException;

class NotFoundOnCompile extends RuntimeException
{
    /**
     * @param string $container
     * @param array<string> $stack
     */
    public function __construct(string $container, array $stack)
    {
        parent::__construct(
            message: sprintf(
                '`%s` not found on compile, stack: %s',
                $container,
                implode(', ', $stack)
            ),
        );
    }
}
