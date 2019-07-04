<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Exception;

use Cekta\DI\ProviderException;
use Exception;

class NotFound extends Exception implements ProviderException
{
    public function __construct(string $id)
    {
        parent::__construct("Container `$id` not found");
    }
}
