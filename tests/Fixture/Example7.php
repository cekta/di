<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class Example7
{
    public A|int $param;

    public function __construct(A|int $param)
    {
        $this->param = $param;
    }
}
