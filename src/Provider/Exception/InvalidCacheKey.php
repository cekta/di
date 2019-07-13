<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Exception;

use Cekta\DI\ProviderException;
use RuntimeException;
use Throwable;

class InvalidCacheKey extends RuntimeException implements ProviderException
{
    public function __construct(string $id, Throwable $previous)
    {
        $message = "Invalide cache key $id";
        parent::__construct($message, 0, $previous);
    }

}
