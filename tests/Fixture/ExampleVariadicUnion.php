<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleVariadicUnion
{
    public array $param;

    public function __construct(A|int ...$param)
    {
        $this->param = $param;
    }
}
