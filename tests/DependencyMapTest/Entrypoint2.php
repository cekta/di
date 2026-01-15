<?php

declare(strict_types=1);

namespace Cekta\DI\Test\DependencyMapTest;

readonly class Entrypoint2
{
    public function __construct(
        public SomeSharedDependency $dependency
    ) {
    }
}
