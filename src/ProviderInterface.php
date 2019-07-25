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
     */
    public function provide(string $id, ContainerInterface $container);

    public function canBeProvided(string $id): bool;
}
