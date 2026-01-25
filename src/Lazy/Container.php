<?php

declare(strict_types=1);

namespace Cekta\DI\Lazy;

use Cekta\DI\Lazy;
use Psr\Container\ContainerInterface;

class Container implements Lazy
{
    public function load(ContainerInterface $container): ContainerInterface
    {
        return $container;
    }
}
