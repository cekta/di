<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class B
{
    public string $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }
}
