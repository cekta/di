<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class Shared
{
    public function __construct(
        public S $s,
        public string $username,
        public string $dsn,
    ) {
    }
}
