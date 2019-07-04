<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire;

interface ReaderInterface
{
    /**
     * @param string $className
     * @return array
     * @throws ReaderException
     */
    public function getDependencies(string $className): array;
}
