<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Alias;

class Implementation extends KeyValue
{
    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            if (is_string($value)) {
                $values[$key] = new Alias($value);
            }
        }
        parent::__construct($values);
    }
}
