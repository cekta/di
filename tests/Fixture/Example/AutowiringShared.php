<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture\Example;

use Cekta\DI\Test\Fixture\D;

class AutowiringShared
{
    public function __construct(
        public Shared $shared,
        public D $d,
    ) {
    }
}
