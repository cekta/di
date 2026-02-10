<?php

declare(strict_types=1);

namespace Cekta\DI\Test\ContainerBuilderTest;

class EntrypointCircularDependency
{
    public function __construct(
        public CircularDependency $b,
    ) {
    }
}
