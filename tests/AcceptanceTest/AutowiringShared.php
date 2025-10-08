<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class AutowiringShared
{
    public function __construct(
        public Shared $shared,
        public D $d,
    ) {
    }
}
