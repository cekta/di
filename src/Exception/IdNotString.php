<?php
declare(strict_types=1);

namespace Cekta\DI\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class IdNotString extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct()
    {
        parent::__construct('Container ID must be string');
    }
}
