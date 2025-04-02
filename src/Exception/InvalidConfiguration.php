<?php

declare(strict_types=1);

namespace Cekta\DI\Exception;

class InvalidConfiguration extends \InvalidArgumentException
{
    public function __construct(array $diff)
    {
        parent::__construct(sprintf('Container: %s must be defined', implode(', ', $diff)));
    }
}
