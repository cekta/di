<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointAutowiring
{
    /**
     * @var int[]
     */
    public array $variadic_int;

    public function __construct(
        public string $username,
        public string $password,
        public ContainerCreatedWithNew $created_with_new,
        public I $i,
        public S $s,
        public S $s2,
        public S $s3,
        public S $s4,
        public S | string $union_type,
        public string $dsn,
        public string $argument_to_custom_param,
        public string $argument_to_custom_alias,
        public EntrypointSharedDependency $exampleShared,
        int ...$variadic_int
    ) {
        $this->variadic_int = $variadic_int;
    }
}
