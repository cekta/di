<?php

declare(strict_types=1);

namespace Cekta\DI;

readonly class FQCN
{
    public string $className;
    public string $namespace;

    public function __construct(string $fqcn)
    {
        $parts = explode('\\', $fqcn);
        $this->className = array_pop($parts);
        $this->namespace = implode('\\', $parts);
    }
}
