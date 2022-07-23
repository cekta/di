<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class Example3
{
    public string $username;
    public A $a;
    public B $b;

    public function __construct(string $username, A $a, B $b)
    {
        $this->username = $username;
        $this->a = $a;
        $this->b = $b;
    }
}
