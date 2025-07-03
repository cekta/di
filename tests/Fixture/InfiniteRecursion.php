<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

use Cekta\DI\Test\Fixture\Example\InfiniteRecursionExample;

class InfiniteRecursion
{
    public function __construct(
        public InfiniteRecursionExample $infinite_recursion_example
    ) {
    }
}
