<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\Reflection;
use Psr\Container\ContainerInterface;

class Autowiring implements ContainerInterface
{
    private Reflection $reflection;
    private ContainerInterface $container;

    public function __construct(Reflection $reflection, ContainerInterface $container)
    {
        $this->reflection = $reflection;
        $this->container = $container;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFound($id);
        }
        $args = [];
        foreach ($this->reflection->getDependencies($id) as $dependency) {
            $args[] = $this->container->get($dependency);
        }
        return new $id(...$args);
    }

    public function has($id): bool
    {
        return $this->reflection->isInstantiable($id);
    }
}
