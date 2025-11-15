<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointBugOfAlias
{
    public function __construct(
        public string $some_argument_name
    ) {
    }
}
