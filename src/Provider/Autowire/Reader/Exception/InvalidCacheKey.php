<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader\Exception;

use Cekta\DI\Provider\Autowire\ReaderException;
use Exception;
use Throwable;

class InvalidCacheKey extends Exception implements ReaderException
{
    public function __construct(string $className, string $key, Throwable $previous = null)
    {
        $message = "Invalid key: `$key` for class: `$className`";
        parent::__construct($message, 0, $previous);
    }
}
