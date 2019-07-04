<?php
declare(strict_types=1);

namespace Cekta\DI\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class IdNotString extends Exception implements ContainerExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Container ID must be string');
    }
}
