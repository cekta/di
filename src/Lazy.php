<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

/**
 * @external
 */
interface Lazy
{
    public function load(ContainerInterface $container): mixed;
}
