<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class Example2
{
    public $username;

    public function __construct($username)
    {
        $this->username = $username;
    }
}
