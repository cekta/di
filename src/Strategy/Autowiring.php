<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\Reflection;
use InvalidArgumentException;
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
        $args = [];
        foreach ($this->reflection->getDependencies($id) as $dependency) {
            $container = $this->container->get($dependency['name']);
            if ($dependency['variadic'] === true) {
                assert(is_array($container));
                $args = array_merge($args, $container);
            } else {
                $args[] = $container;
            }
        }
        return new $id(...$args);
    }

    public function has($id): bool
    {
        return $this->reflection->isInstantiable($id);
    }
}
