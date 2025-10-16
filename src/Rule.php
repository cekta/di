<?php

declare(strict_types=1);

namespace Cekta\DI;

/**
 * @external
 */
interface Rule
{
    /**
     * @param string $container
     * @param DependencyDTO[] $dependencies
     * @return DependencyDTO[] modified dependencies list
     */
    public function apply(string $container, array $dependencies): array;
}
