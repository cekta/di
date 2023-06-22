<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleVariadicOverwrite
{
    public array $params;

    public function __construct(string ...$variadic_primitive_params)
    {
        $this->params = $variadic_primitive_params;
    }
}
