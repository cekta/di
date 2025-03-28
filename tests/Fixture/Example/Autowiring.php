<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture\Example;

use Cekta\DI\Test\Fixture\A;
use Cekta\DI\Test\Fixture\I;
use Cekta\DI\Test\Fixture\S;

class Autowiring
{
    /**
     * @var int[]
     */
    public array $variadic_int;

    public function __construct(
        public string $username,
        public string $password,
        public Shared $exampleShared, // other entrypoints must be available
        public A $a,  // container only for this container must be created with new
        public I $i, // interface
        public S $s, // shared containers must be reusable
        public S | string $named, // named container must work start with php8.0
        public string $definition,
        int ...$variadic_int
    ) {
        $this->variadic_int = $variadic_int;
    }
}
