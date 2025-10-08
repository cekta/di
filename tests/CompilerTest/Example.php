<?php

declare(strict_types=1);

namespace Cekta\DI\Test\CompilerTest;

use Iterator;

class Example
{
    public function __construct(public Iterator $iterator)
    {
    }
}
