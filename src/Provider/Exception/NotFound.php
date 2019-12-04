<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Exception;

use Cekta\DI\ProviderExceptionInterface;
use RuntimeException;

class NotFound extends RuntimeException implements ProviderExceptionInterface
{
    public function __construct(string $id)
    {
        parent::__construct("Container `${id}` not found");
    }
}
