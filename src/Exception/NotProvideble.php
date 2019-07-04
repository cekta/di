<?php
declare(strict_types=1);

namespace Cekta\DI\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

class NotProvideble extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $id, Throwable $previous)
    {
        parent::__construct("Provider cant load container `$id`", 0, $previous);
    }
}
