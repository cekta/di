<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class Example8
{
    public A&I $param;

    public function __construct(A&I $param)
    {
        $this->param = $param;
    }
}
