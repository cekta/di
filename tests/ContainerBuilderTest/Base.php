<?php

declare(strict_types=1);

namespace Cekta\DI\Test\ContainerBuilderTest;

abstract class Base
{
    public function __construct(public string $username)
    {
    }
}
