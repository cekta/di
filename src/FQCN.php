<?php

declare(strict_types=1);

namespace Cekta\DI;

class FQCN
{
    private string $fqcn;

    public function __construct(string $fqcn)
    {
        $this->fqcn = $fqcn;
    }

    public function getNamespace(): string
    {
        $position = strrpos($this->fqcn, '\\');
        if ($position === false) {
            return '';
        }
        return substr($this->fqcn, 0, $position);
    }

    public function getClass(): string
    {
        $position = 0;
        if (strrpos($this->fqcn, '\\') !== false) {
            $position = strrpos($this->fqcn, '\\') + 1;
        }
        return substr($this->fqcn, $position);
    }
}
