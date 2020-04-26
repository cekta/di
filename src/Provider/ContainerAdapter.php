<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider;
use Psr\Container\ContainerInterface;

class ContainerAdapter implements Provider
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function provide(string $id)
    {
        return $this->container->get($id);
    }

    public function canProvide(string $id): bool
    {
        return $this->container->has($id);
    }
}
