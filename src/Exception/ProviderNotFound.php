<?php
declare(strict_types=1);

namespace Cekta\DI\Exception;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

class ProviderNotFound extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct("Provider not found for `$id`");
    }
}
