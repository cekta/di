<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointApplyRule
{
    public function __construct(
        public string $username,
        public string $password
    ) {
    }
}
