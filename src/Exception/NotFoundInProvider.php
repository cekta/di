<?php
declare(strict_types=1);

namespace Cekta\DI\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use Throwable;

class NotFoundInProvider extends Exception implements ContainerExceptionInterface
{
    public function __construct(string $name, Throwable $previous)
    {
        parent::__construct("Provider cant load container `$name`", 0, $previous);
    }
}
