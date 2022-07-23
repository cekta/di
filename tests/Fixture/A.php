<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class A
{
    public B $b;

    public function __construct(B $b)
    {
        $this->b = $b;
    }
}
