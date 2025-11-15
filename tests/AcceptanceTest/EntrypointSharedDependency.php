<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointSharedDependency
{
    /**
     * @var int[]
     */
    public array $variadic_int;

    public function __construct(
        public S $s,
        public string $argument_to_custom_param,
        public string $argument_to_custom_alias,
        public string $argument_to_custom_alias2,
        int ...$variadic_int
    ) {
        $this->variadic_int = $variadic_int;
    }
}
