<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleParams
{
    public int $a;
    public int $b;

    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
