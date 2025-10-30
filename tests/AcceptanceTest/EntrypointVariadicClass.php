<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointVariadicClass
{
    /**
     * @var A[]
     */
    public array $a_array;

    public function __construct(A ...$a_array)
    {
        $this->a_array = $a_array;
    }
}
