<?php

namespace Cekta\DI\Test;

class C
{
    /**
     * @var int
     */
    private $a;
    /**
     * @var int
     */
    private $b;

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

    /**
     * @return int
     */
    public function getA(): int
    {
        return $this->a;
    }

    /**
     * @return int
     */
    public function getB(): int
    {
        return $this->b;
    }
}
