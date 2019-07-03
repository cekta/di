<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;

interface ReaderInterface
{
    /**
     * @param string $className
     * @return array
     * @throws InvalidClassName
     */
    public function getDependencies(string $className): array;
}
