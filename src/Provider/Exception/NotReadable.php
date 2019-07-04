<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Exception;

use Cekta\DI\ProviderException;
use Exception;
use Throwable;

class NotReadable extends Exception implements ProviderException
{
    public function __construct(string $id, Throwable $throwable)
    {
        parent::__construct("Container `$id` not readable", 0, $throwable);
    }
}
