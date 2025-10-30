<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use RuntimeException;
use Throwable;

class InvalidContainerForCompile extends RuntimeException
{
    /**
     * @param string $container
     * @param array<string> $stack
     * @param Throwable|null $throwable
     */
    public function __construct(string $container, array $stack, ?Throwable $throwable = null)
    {
        parent::__construct(
            message: sprintf(
                'Invalid container:`%s` for compile, stack: %s',
                $container,
                implode(', ', $stack)
            ),
            previous: $throwable
        );
    }
}
