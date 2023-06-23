<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy;

use Cekta\DI\Reflection;
use Psr\Container\ContainerInterface;

class Autowiring implements ContainerInterface
{
    private Reflection $reflection;
    private ContainerInterface $container;
    /**
     * @var array<string, string>
     */
    private array $alias;

    /**
     * @param Reflection $reflection
     * @param ContainerInterface $container
     * @param array<string, string> $alias
     */
    public function __construct(Reflection $reflection, ContainerInterface $container, array $alias)
    {
        $this->reflection = $reflection;
        $this->container = $container;
        $this->alias = $alias;
    }

    public function get($id)
    {
        $args = [];
        foreach ($this->reflection->getDependencies($id) as $dependency) {
            $target = $dependency['name'];
            if (array_key_exists($dependency['parameter'], $this->alias)) {
                $target = $dependency['parameter'];
            }

            $container = $this->container->get($target);

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
