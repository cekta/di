<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleWithoutType
{
    public mixed $username;

    public function __construct($username)
    {
        $this->username = $username;
    }
}
