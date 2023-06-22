<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

class ExampleVariadicNamedType
{
    /**
     * @var ExampleWithoutConstructor[]
     */
    public array $param;

    public function __construct(ExampleWithoutConstructor ...$param)
    {
        $this->param = $param;
    }
}
