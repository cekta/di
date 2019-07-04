<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader\Exception;

use Cekta\DI\Provider\Autowire\ReaderException;
use Exception;

class InvalidClassName extends Exception implements ReaderException
{
    public function __construct(string $className)
    {
        $message = "Invalid class name: `{$className}`";
        parent::__construct($message);
    }
}
