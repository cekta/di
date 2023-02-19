<?php

namespace Cekta\DI;

use Cekta\DI\Strategy\Autowiring;
use Cekta\DI\Strategy\Definition;
use Cekta\DI\Strategy\Implementation;
use Cekta\DI\Strategy\KeyValue;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private ContainerInterface $container;

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $interfaces
     * @param array<string, callable> $definitions
     */
    public function __construct(array $params = [], array $interfaces = [], array $definitions = [])
    {
        $this->container = new InfiniteRecursionDetector(new ArrayCache(new Strategy(
            new KeyValue($params),
            new Definition($definitions, $this),
            new Implementation($interfaces, $this),
            new Autowiring(new Reflection(), $this),
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
