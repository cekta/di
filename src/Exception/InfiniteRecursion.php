<?php
declare(strict_types=1);

namespace Cekta\DI\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class InfiniteRecursion extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $id, array $calls)
    {
        $callsString = implode(', ', $calls);
        parent::__construct("Infinite recursion for `$id`, calls: `$callsString`");
    }
}
