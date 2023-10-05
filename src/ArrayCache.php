<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

class ArrayCache implements ContainerInterface
{
    private ContainerInterface $container;
    private array $values = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $id)
    {
        if (!array_key_exists($id, $this->values)) {
            $this->values[$id] = $this->container->get($id);
        }
        return $this->values[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->values) || $this->container->has($id);
    }
}
