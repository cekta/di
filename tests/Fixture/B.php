<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class B
{
    public function __construct(
        private string $username,
        private string $password,
        private C $c
    ) {
    }
}
