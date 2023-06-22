<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleDNFType
{
    public (A & B) | int $param;

    public function __construct((A & B) | int $param)
    {
        $this->param = $param;
    }
}
