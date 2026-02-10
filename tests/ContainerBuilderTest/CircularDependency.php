<?php

declare(strict_types=1);

namespace Cekta\DI\Test\ContainerBuilderTest;

class CircularDependency
{
    public function __construct(
        public EntrypointCircularDependency $infinite_recursion_example
    ) {
    }
}
