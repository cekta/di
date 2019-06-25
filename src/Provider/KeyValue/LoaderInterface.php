<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\KeyValue;

use Psr\Container\ContainerInterface;

interface LoaderInterface
{
    public function __invoke(ContainerInterface $container);
}
