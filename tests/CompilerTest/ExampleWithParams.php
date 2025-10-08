<?php

declare(strict_types=1);

namespace Cekta\DI\Test\CompilerTest;

class ExampleWithParams
{
    public function __construct(
        private string $username,
        private string $password,
    ) {
    }
}
