<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class InfiniteRecursion extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $id, array $calls)
    {
        $callsString = implode(', ', $calls);
        parent::__construct("Infinite recursion for `${id}`, calls: `${callsString}`");
    }
}
