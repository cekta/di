<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader\Exception;

use RuntimeException;
use Throwable;

class InvalidCacheKey extends RuntimeException
{
    public function __construct(string $className, string $key, Throwable $previous = null)
    {
        $message = "Invalid key: `$key` for class: `$className`";
        parent::__construct($message, 0, $previous);
    }
}
