<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Exception;

use Cekta\DI\ProviderNotFoundException;
use Exception;
use Throwable;

class NotFound extends Exception implements ProviderNotFoundException
{
    public function __construct(string $name, Throwable $previous)
    {
        parent::__construct("Container `$name` not found", 0, $previous);
    }
}
