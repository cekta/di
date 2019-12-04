<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class ProviderNotFound extends RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct("Provider not found for `${id}`");
    }
}
