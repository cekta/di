<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Exception;

use Cekta\DI\ProviderException;
use RuntimeException;

class NotFound extends RuntimeException implements ProviderException
{
    public function __construct(string $id)
    {
        parent::__construct("Container `$id` not found");
    }
}
