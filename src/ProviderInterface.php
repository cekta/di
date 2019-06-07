<?php
declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

interface ProviderInterface
{
    public function provide(string $name, ContainerInterface $container);

    public function hasProvide(string $name): bool;
}
