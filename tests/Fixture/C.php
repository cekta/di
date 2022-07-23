<?php

namespace Cekta\DI\Test\Fixture;

class C
{
    /**
     * @var int
     */
    public int $a;
    /**
     * @var int
     */
    public int $b;

    /**
     * @param int $a
     * @param int $b
     * @inject a\magic $a
     * @inject b\magic $b
     */
    public function __construct(int $a, int $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
