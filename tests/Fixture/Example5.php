<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class Example5
{
    public string $username;
    public string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}
