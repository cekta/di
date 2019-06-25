<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\KeyValue\Exception;

use Cekta\DI\ProviderNotFoundException;
use Exception;

class NotFound extends Exception implements ProviderNotFoundException
{
    public function __construct(string $name)
    {
        parent::__construct("Container `$name` not found");
    }
}
