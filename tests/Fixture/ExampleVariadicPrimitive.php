<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleVariadicPrimitive
{
    public array $params;
    public string $username;

    public function __construct(string $username, string ...$variadic_strings)
    {
        $this->params = $variadic_strings;
        $this->username = $username;
    }
}
