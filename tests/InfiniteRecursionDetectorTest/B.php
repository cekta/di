<?php

declare(strict_types=1);

namespace Cekta\DI\Test\InfiniteRecursionDetectorTest;

class B
{
    public function __construct(
        public A $infinite_recursion_example
    ) {
    }
}
