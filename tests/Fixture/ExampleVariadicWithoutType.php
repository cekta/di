<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleVariadicWithoutType
{
    public array $params;

    public function __construct(...$variadic_params)
    {
        $this->params = $variadic_params;
    }
}
