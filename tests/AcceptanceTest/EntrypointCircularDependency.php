<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointCircularDependency
{
    public function __construct(
        public CircularDependency $b,
    ) {
    }
}
