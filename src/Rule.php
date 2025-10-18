<?php

declare(strict_types=1);

namespace Cekta\DI;

/**
 * @external
 */
interface Rule
{
    /**
     * @param string $container_name
     * @param string $dependency_name
     * @return string actual dependency name
     */
    public function apply(string $container_name, string $dependency_name): string;
}
