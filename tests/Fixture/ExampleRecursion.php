<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleRecursion
{
    public ExampleRecursion $recursion;

    public function __construct(ExampleRecursion $recursion)
    {
        $this->recursion = $recursion;
    }
}
