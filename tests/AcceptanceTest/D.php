<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class D
{
    public function __construct(
        public I $i
    ) {
    }
}
