<?php
declare(strict_types=1);

namespace Cekta\DI\Exception;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

class NotFound extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public function __construct(string $name)
    {
        parent::__construct("Container `$name` not found");
    }
}
