<?php

namespace Cekta\DI;

use Cekta\DI\Strategy\Autowiring;
use Cekta\DI\Strategy\Definition;
use Cekta\DI\Strategy\Alias;
use Cekta\DI\Strategy\KeyValue;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private ContainerInterface $container;

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $alias
     * @param array<string, callable> $definitions
     */
    public function __construct(array $params = [], array $alias = [], array $definitions = [])
    {
        $this->container = new ArrayCache(new InfiniteRecursionDetector(new Strategy(
            new KeyValue($params),
            new Definition($definitions, $this),
            new Alias($alias, $this),
            new Autowiring(new Reflection(), $this, $alias),
        )));
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id): bool
    {
        return $this->container->has($id);
    }
}
