<?php
declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

interface ProviderInterface
{
    /**
     * @param string $id
     * @param ContainerInterface $container
     * @return mixed
     * @throws ProviderNotFoundException
     */
    public function provide(string $id, ContainerInterface $container);

    public function canProvide(string $id): bool;
}
