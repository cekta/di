<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

interface LoaderInterface
{
    public function __invoke(ContainerInterface $container);
}
