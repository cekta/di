<?php

declare(strict_types=1);

namespace Cekta\DI\Test\ContainerBuilderTest;

class SWithParam extends S
{
    public function __construct(public string $name)
    {
    }
}
