<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class SWithParam extends S
{
    public function __construct(public string $name)
    {
    }
}
