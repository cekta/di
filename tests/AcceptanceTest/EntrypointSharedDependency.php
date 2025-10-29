<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointSharedDependency
{
    public function __construct(
        public S $s,
    ) {
    }
}
