<?php

declare(strict_types=1);

namespace Cekta\DI\Test\InfiniteRecursionDetectorTest;

class A
{
    public function __construct(
        public B $b,
    ) {
    }
}
