<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class Example
{
    public A $a;
    public I $i;

    public function __construct(A $a, I $i)
    {
        $this->a = $a;
        $this->i = $i;
    }
}
