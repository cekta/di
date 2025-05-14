<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture\Example;

use Cekta\DI\Test\Fixture\S;

class Shared
{
    public function __construct(
        public S $s,
        public string $username,
        public string $definition,
    ) {
    }
}
