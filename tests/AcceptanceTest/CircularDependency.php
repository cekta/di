<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class CircularDependency
{
    public function __construct(
        public EntrypointCircularDependency $infinite_recursion_example
    ) {
    }
}
