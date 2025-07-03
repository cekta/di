<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture\Example;

use Cekta\DI\Test\Fixture\InfiniteRecursion;
use Cekta\DI\Test\Fixture\S;

class InfiniteRecursionExample
{
    public function __construct(
        public Shared $shared,
        public S $s,
        public InfiniteRecursion $infinite_recursion,
    ) {
    }
}
