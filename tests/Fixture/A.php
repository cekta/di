<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class A
{
    public string $dsn;
    public string $username;
    public string $password;

    public function __construct(string $dsn, string $username, string $password = 'default password')
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }
}
