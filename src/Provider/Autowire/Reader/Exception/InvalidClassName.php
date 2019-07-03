<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader\Exception;

use RuntimeException;
use Throwable;

class InvalidClassName extends RuntimeException
{
    public function __construct(string $className, Throwable $previous = null)
    {
        $message = "Invalid class name: `{$className}`";
        parent::__construct($message, 0, $previous);
    }
}
