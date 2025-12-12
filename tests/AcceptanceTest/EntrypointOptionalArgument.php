<?php

declare(strict_types=1);

namespace Cekta\DI\Test\AcceptanceTest;

class EntrypointOptionalArgument
{
    /**
     * @var int[]
     */
    public readonly array $etc;
    public function __construct(
        public I $i,
        public S $s = new SWithParam('default param'),
        public string $string_default = 'default value',
        public string $must_continue_not_break = 'other value',
        int ...$etc,
    ) {
        $this->etc = $etc;
    }
}
