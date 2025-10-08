<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class ExamplePopShared
{
    public function __construct(
        public S $s,
        public S $s2
    ) {
    }
}
