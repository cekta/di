<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class R implements I
{
    public A $a;

    public function __construct(A $a)
    {
        $this->a = $a;
    }
}
