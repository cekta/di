<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleVariadicIntersection
{
    public array $param;

    public function __construct(A&I ...$param)
    {
        $this->param = $param;
    }
}
